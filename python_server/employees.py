from flask_restful import Resource
from flask import request, make_response, jsonify
from werkzeug.security import generate_password_hash
import pymysql
import pymysql.err as py_err
import os

from app import get_shared_cursor, parse_row_list, token_required, mysql

class EmployeeManagement(Resource):

    @token_required
    def get(self):
        """Retrieve employees. Supports filters: id, search(username), page/paginate, active, role_type, total."""
        q = request.args

        # filters
        emp_id = None
        try:
            if q.get('id') is not None:
                emp_id = int(str(q.get('id')).strip())
        except (ValueError, TypeError):
            emp_id = None

        username = (q.get('search') or q.get('username') or '').strip() or None

        # pagination
        try:
            page = int(q.get('page', 1))
        except (TypeError, ValueError):
            page = 1
        try:
            paginate = int(q.get('paginate', 10))
        except (TypeError, ValueError):
            paginate = 10
        if paginate < 1 or paginate > 1000:
            paginate = 10
        if page < 1:
            page = 1

        # build query
        sql = "SELECT id, username, role_type, email, active, created_at, employee_last_login FROM employees WHERE 1=1"
        params = []
        if emp_id is not None:
            sql += " AND id = %s"
            params.append(emp_id)
        else:
            if username is not None:
                sql += " AND username LIKE %s"
                params.append(f"%{username}%")

        if 'total' in q:
            sql = "SELECT COUNT(id) AS total_employees FROM employees WHERE 1=1"
            params = []
            if username is not None:
                sql += " AND username LIKE %s"
                params.append(f"%{username}%")

        if emp_id is None and 'total' not in q:
            offset = (page - 1) * paginate
            sql += f" LIMIT {paginate} OFFSET {offset}"
        else:
            sql += " LIMIT 1"

        try:
            # create cursor per-request to avoid stale connections
            cursor = get_shared_cursor()
            cursor.execute(sql, tuple(params))
            rows = cursor.fetchall()
        except Exception as e:
            return make_response(jsonify({'status': 'error', 'message': str(e)}), 500)

        if 'total' in q:
            total = rows[0]['total_employees'] if rows else 0
            return make_response(jsonify({'status': 'success', 'total_employees': total}), 200)

        keys = ['id', 'username', 'role_type', 'email', 'active', 'created_at', 'employee_last_login']
        data = parse_row_list(rows, keys)

        if emp_id is not None:
            if not data:
                return make_response(jsonify({'status': 'error', 'message': 'Employee not found'}), 404)
            return make_response(jsonify({'status': 'success', 'employee': data[0], 'message': 'Employee found'}), 200)

        if data:
            return make_response(jsonify({'status': 'success', 'employees': data, 'message': 'Employees found'}), 200)
        return make_response(jsonify({'status': 'error', 'message': 'No employees found'}), 200)

    def post(self):
        """Create employee: username, password, email, role_type, active"""
        json_data = request.get_json()
        if not json_data:
            return make_response(jsonify({'status': 'error', 'message': 'Please input for the following'}), 400)

        username = json_data.get('username')
        password = json_data.get('password')
        email = json_data.get('email')
        role_type = json_data.get('role_type')
        active = json_data.get('active', 1)

        missing = []
        if not username:
            missing.append('username')
        if not password:
            missing.append('password')
        if not email:
            missing.append('email')
        if role_type is None:
            missing.append('role_type')
        if active is None:
            missing.append('active')
        if missing:
            return make_response(jsonify({'status': 'error', 'message': f"Please input for the following: {', '.join(missing)}"}), 400)

        try:
            cursor = get_shared_cursor()
            cursor.execute('SELECT id FROM employees WHERE username = %s LIMIT 1', (username,))
            if cursor.fetchone():
                return make_response(jsonify({'status': 'error', 'message': 'Username already exists'}), 409)

            hashed = generate_password_hash(password)
            cursor.execute('INSERT INTO employees (username, password, email, role_type, active, created_at) VALUES (%s, %s, %s, %s, %s, NOW())', (username, hashed, email, role_type, active))
            mysql.commit()
            new_id = getattr(cursor, 'lastrowid', None)
            payload = {'status': 'success', 'message': f'Employee {username} registered successfully'}
            if new_id:
                payload['data'] = {'id': int(new_id)}
            return make_response(jsonify(payload), 201)
        except py_err.OperationalError as e:
            try:
                mysql.rollback()
            except Exception:
                pass
            return make_response(jsonify({'status': 'error', 'message': e.args[1] if len(e.args) > 1 else str(e)}), 400)
        except py_err.Error as e:
            try:
                mysql.rollback()
            except Exception:
                pass
            return make_response(jsonify({'status': 'error', 'message': e.args[1] if len(e.args) > 1 else str(e)}), 500)

    @token_required
    def put(self):
        """Full update: requires id, username, password, email, role_type, active"""
        json_data = request.get_json()
        if not json_data:
            return make_response(jsonify({'status': 'error', 'message': 'No data provided'}), 400)

        try:
            emp_id = int(json_data.get('id'))
        except (TypeError, ValueError):
            return make_response(jsonify({'status': 'error', 'message': 'Invalid or missing id'}), 400)

        username = json_data.get('username')
        password = json_data.get('password')
        email = json_data.get('email')
        role_type = json_data.get('role_type')
        active = json_data.get('active')

        missing = []
        if not username:
            missing.append('username')
        if password is None:
            missing.append('password')
        if email is None:
            missing.append('email')
        if role_type is None:
            missing.append('role_type')
        if active is None:
            missing.append('active')
        if missing:
            return make_response(jsonify({'status': 'error', 'message': f"Please input for the following: {', '.join(missing)}"}), 400)

        try:
            cursor = get_shared_cursor()
            # Check username conflict
            cursor.execute('SELECT id FROM employees WHERE username = %s AND id != %s LIMIT 1', (username, emp_id))
            if cursor.fetchone():
                return make_response(jsonify({'status': 'error', 'message': 'Username already exists'}), 409)

            hashed = generate_password_hash(password) if password else None
            cursor.execute('UPDATE employees SET username = %s, password = %s, email = %s, role_type = %s, active = %s, employee_last_login = employee_last_login WHERE id = %s', (username, hashed, email, role_type, active, emp_id))
            mysql.commit()
            if cursor.rowcount > 0:
                return make_response(jsonify({'status': 'success', 'message': 'Employee updated successfully'}), 200)
            return make_response(jsonify({'status': 'error', 'message': 'No changes made'}), 200)
        except py_err.OperationalError as e:
            try:
                mysql.rollback()
            except Exception:
                pass
            return make_response(jsonify({'status': 'error', 'message': e.args[1] if len(e.args) > 1 else str(e)}), 400)
        except py_err.Error as e:
            try:
                mysql.rollback()
            except Exception:
                pass
            return make_response(jsonify({'status': 'error', 'message': e.args[1] if len(e.args) > 1 else str(e)}), 500)

    @token_required
    def patch(self):
        """Partial update: accept id (or user_id) and update only provided fields."""
        json_data = request.get_json()
        if not json_data:
            return make_response(jsonify({'status': 'error', 'message': 'No data provided'}), 400)

        user_id_raw = json_data.get('user_id', json_data.get('id'))
        try:
            user_id = int(user_id_raw)
        except (TypeError, ValueError):
            return make_response(jsonify({'status': 'error', 'message': "Invalid or missing 'user_id' field"}), 400)

        try:
            cursor = get_shared_cursor()
            cursor.execute('SELECT id, username FROM employees WHERE id = %s LIMIT 1', (user_id,))
            row = cursor.fetchone()
            if not row:
                return make_response(jsonify({'status': 'error', 'message': 'User not found'}), 404)

            updates = []
            params = []

            if 'username' in json_data and (json_data.get('username') or '').strip():
                new_username = str(json_data.get('username')).strip()
                cursor.execute('SELECT 1 FROM employees WHERE username = %s AND id <> %s LIMIT 1', (new_username, user_id))
                if cursor.fetchone():
                    return make_response(jsonify({'status': 'error', 'message': 'Username already exists'}), 409)
                updates.append('username = %s')
                params.append(new_username)

            if 'password' in json_data and (json_data.get('password') or '').strip():
                new_password = str(json_data.get('password')).strip()
                hashed = generate_password_hash(new_password)
                updates.append('password = %s')
                params.append(hashed)

            if 'role_type' in json_data and json_data.get('role_type') is not None:
                updates.append('role_type = %s')
                params.append(json_data.get('role_type'))

            if 'active' in json_data:
                raw = json_data.get('active')
                active_val = None
                if isinstance(raw, bool):
                    active_val = 1 if raw else 0
                elif isinstance(raw, (int, float)):
                    active_val = 1 if int(raw) == 1 else 0
                elif isinstance(raw, str):
                    s = raw.strip().lower()
                    if s in ('1', 'true', 'yes', 'y', 'active'):
                        active_val = 1
                    elif s in ('0', 'false', 'no', 'n', 'inactive'):
                        active_val = 0
                if active_val is not None:
                    updates.append('active = %s')
                    params.append(active_val)

            if not updates:
                return make_response(jsonify({'status': 'error', 'message': 'No fields to update'}), 400)

            updates.append('employee_last_login = employee_last_login')
            sql = f"UPDATE employees SET {', '.join(updates)} WHERE id = %s"
            params.append(user_id)
            cursor.execute(sql, tuple(params))
            mysql.commit()
            return make_response(jsonify({'status': 'success', 'message': 'User updated successfully'}), 200)
        except py_err.OperationalError as e:
            try:
                mysql.rollback()
            except Exception:
                pass
            return make_response(jsonify({'status': 'error', 'message': e.args[1] if len(e.args) > 1 else str(e)}), 400)
        except py_err.Error as e:
            try:
                mysql.rollback()
            except Exception:
                pass
            return make_response(jsonify({'status': 'error', 'message': e.args[1] if len(e.args) > 1 else str(e)}), 500)

    def delete(self):
        json_data = request.get_json()
        if not json_data:
            return make_response(jsonify({'status': 'error', 'message': 'No data provided'}), 400)
        try:
            user_id = int(json_data.get('user_id'))
        except (TypeError, ValueError):
            return make_response(jsonify({'status': 'error', 'message': "Invalid or missing 'user_id' field"}), 400)

        try:
            cursor = get_shared_cursor()
            cursor.execute('SELECT 1 FROM employees WHERE id = %s LIMIT 1', (user_id,))
            if not cursor.fetchone():
                return make_response(jsonify({'status': 'error', 'message': 'User not found'}), 404)
            cursor.execute('DELETE FROM employees WHERE id = %s', (user_id,))
            mysql.commit()
            return make_response(jsonify({'status': 'success', 'message': 'User deleted successfully'}), 200)
        except py_err.OperationalError as e:
            try:
                mysql.rollback()
            except Exception:
                pass
            return make_response(jsonify({'status': 'error', 'message': e.args[1] if len(e.args) > 1 else str(e)}), 400)
        except py_err.Error as e:
            try:
                mysql.rollback()
            except Exception:
                pass
            return make_response(jsonify({'status': 'error', 'message': e.args[1] if len(e.args) > 1 else str(e)}), 500)

    def options(self):
        response_data = {
            'status': 'success',
            'message': 'Options retrieved successfully',
            'data': {'methods': ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS']}
        }
        return make_response(jsonify(response_data), 200)

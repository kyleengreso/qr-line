from flask import Flask, jsonify, request
from flask_restful import Api, Resource
import os, pymysql
from functools import wraps

from flask import Flask, render_template, request, redirect, url_for, jsonify, make_response
from flask_sqlalchemy import SQLAlchemy
from werkzeug.security import generate_password_hash, check_password_hash
import jwt
import uuid
from datetime import datetime, timezone, timedelta
from functools import wraps

APP_DEBUG = True
APP_PORT = 5000

# API Init
app = Flask(__name__)
api = Api(app)

from flask_cors import CORS, cross_origin

app.config['CORS_HEADERS'] = 'Content-Type'

ALLOWED_ORIGINS = [
    "http://localhost:5000",
    "https://qrline.miceff.com",
    "https://acedaybase.pythonanywhere.com",
]

cors = CORS(app, 
            resources={
                r"/api/*": {
                    "origins": ALLOWED_ORIGINS
                }
            }, supports_credentials=True)



@app.after_request
def add_cors_headers(response):
    origin = request.headers.get('Origin')
    if origin:
        response.headers['Access-Control-Allow-Origin'] = origin
        response.headers['Access-Control-Allow-Credentials'] = 'true'
        response.headers['Access-Control-Allow-Headers'] = 'Content-Type, Authorization'
        response.headers['Access-Control-Allow-Methods'] = 'GET, POST, PUT, PATCH, DELETE, OPTIONS'
    return response


# --- Database helpers moved from employees.py ---
# Expose a module-level `mysql` variable (may be None until get_shared_cursor() is called)
mysql = None

# DB config (from python_server/config.py)
try:
    from config import MYSQL_HOST, MYSQL_PORT, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE
except Exception:
    MYSQL_HOST = os.getenv('MYSQL_HOST', '127.0.0.1')
    MYSQL_PORT = int(os.getenv('MYSQL_PORT', 3306))
    MYSQL_USER = os.getenv('MYSQL_USER', 'root')
    MYSQL_PASSWORD = os.getenv('MYSQL_PASSWORD', 'root')
    MYSQL_DATABASE = os.getenv('MYSQL_DATABASE', 'qr_line')


def get_shared_cursor():
    """Open a new DB connection and return a cursor. The caller may use the
    module-level `mysql` connection for commit/rollback. This mirrors the simple
    behaviour expected by the PHP reference (cur = get_shared_cursor()).
    """
    global mysql
    conn = pymysql.connect(
        host=MYSQL_HOST,
        port=MYSQL_PORT,
        user=MYSQL_USER,
        password=MYSQL_PASSWORD,
        db=MYSQL_DATABASE,
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor,
        autocommit=False,
    )
    mysql = conn
    return conn.cursor()


def parse_row_list(values, keys):
    """Normalize DB rows (dicts or sequences) into list of dicts with given keys."""
    out = []
    for row in values:
        if row is None:
            continue
        if isinstance(row, dict):
            r = {k: row.get(k) for k in keys}
        else:
            r = {k: (row[i] if i < len(row) else None) for i, k in enumerate(keys)}
        out.append(r)
    return out


def token_required(f):
    """Simple token decorator. If REQUIRE_TOKEN=1 in env, require an Authorization
    header; otherwise act as a no-op (helpful for local dev).
    """
    @wraps(f)
    def decorated(*args, **kwargs):
        if os.getenv('REQUIRE_TOKEN', '') == '1':
            auth = request.headers.get('Authorization') or request.headers.get('X-Auth-Token')
            if not auth:
                return make_response(jsonify({'status': 'error', 'message': 'No token provided'}), 401)
        return f(*args, **kwargs)
    return decorated




# PARAM
"""
    DATE_STRFTIME
    %Y =    2025
    %y =    25
    %M =    57
    %m =    08
    %D =    08/01/25
    %d =    22

    %H =    21
    %h =    Aug
    %M =    57
    %m =    08
    %S =    31
    %s =    1755871051
"""
DT_STRFTIME="%Y-%m-%d %H:%M:%S"


# INIT
if __name__ == "__main__":
    app.run(
        debug=APP_DEBUG,
        port=APP_PORT
    )

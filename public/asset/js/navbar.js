
function logOut() {
    // Navigate to server-side logout which will call the API and clear local cookie.
    window.location.href = '/public/auth/logout.php';
}

function clearLocalTokenAndRedirect() {
    // Not used anymore — local clearing handled by server-side logout.php
    window.location.href = '/public/auth/logout.php';
}

// Employee Logout Notify

// DOM document ready no jquery
document.addEventListener("DOMContentLoaded", function () {
    let logOutNotify = document.getElementById('logOutNotify');
    
    let btnLogout1 = document.getElementById("btn-logout-1");
    if (btnLogout1) {
        btnLogout1.addEventListener("click", function () {
            if (logOutNotify) {
                logOutNotify.classList.remove('d-none');
                setTimeout(() => {
                    logOut();
                }, 2000);
            } else {
                // If the page doesn't include the logout notification element,
                // just perform logout immediately.
                logOut();
            }
        });
    };
    
    let btnLogout2 = document.getElementById("btn-logout-2");
    if (btnLogout2) {
        btnLogout2.addEventListener("click", function () {
            if (logOutNotify) {
                logOutNotify.classList.remove('d-none');
                setTimeout(() => {
                    logOut();
                }, 2000);
            } else {
                logOut();
            }
        });
    }

});

// RealTimeClock
function startClock(el) {
    if (!el) return;
    function tick() {
        const date = new Date();
        el.textContent = date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });
    }
    tick();
    setInterval(tick, 1000);
}

document.addEventListener('DOMContentLoaded', function () {
    const clockEl = document.getElementById('current-time') || document.getElementById('rtClock');
    startClock(clockEl);

    // Notification panel setup
    const btnNotifications = document.getElementById('btn-notifications');
    const notificationCount = document.getElementById('notification-count');

    if (!btnNotifications) return;

    // Determine API base URL.
    // Prefer an explicit server-provided `endpointHost` (emitted by PHP pages).
    // Fallback order: window.__API_BASE__ -> window.endpointHost -> location.origin
    const API_BASE = (function(){
        if (window.__API_BASE__) return window.__API_BASE__;
        if (typeof window.endpointHost === 'string' && window.endpointHost.length) {
            return window.endpointHost.replace(/\/$/, '');
        }
        try {
            return window.location.origin;
        } catch (e) {
            return '';
        }
    })();

    // Create modern notification panel
    function createNotificationPanel() {
        let panel = document.getElementById('notification-panel');
        if (panel) return panel;

        panel = document.createElement('div');
        panel.id = 'notification-panel';
        panel.className = 'd-none';
        panel.style.cssText = `
            position: absolute;
            min-width: 360px;
            max-width: 480px;
            max-height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        `;

        document.body.appendChild(panel);
        return panel;
    }

    // Format relative time (e.g., "5m ago")
    function formatTimeAgo(dateStr) {
        if (!dateStr) return '';
        try {
            const date = new Date(dateStr);
            const now = new Date();
            const diffMs = now - date;
            const diffSec = Math.floor(diffMs / 1000);

            if (diffSec < 60) return 'just now';
            if (diffSec < 3600) return `${Math.floor(diffSec / 60)}m ago`;
            if (diffSec < 86400) return `${Math.floor(diffSec / 3600)}h ago`;
            if (diffSec < 604800) return `${Math.floor(diffSec / 86400)}d ago`;
            return date.toLocaleDateString();
        } catch (e) {
            return dateStr;
        }
    }

    // Fetch notifications from API
    async function fetchNotifications() {
        try {
            const resp = await fetch(`${API_BASE}/api/notifications`, { credentials: 'include' });
            if (!resp.ok) return [];
            let body = await resp.json();
            
            // Handle both direct response and wrapped response [data, statusCode]
            if (Array.isArray(body) && body.length > 0) {
                body = body[0]; // Extract the actual response object
            }
            
            return (body && body.notifications) ? body.notifications : [];
        } catch (e) {
            console.error('Error fetching notifications:', e);
            return [];
        }
    }

    // Mark a single notification as opened
    async function markNotificationOpen(id) {
        try {
            const resp = await fetch(`${API_BASE}/api/notifications`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id, action: 'open' })
            });
            return resp.ok;
        } catch (e) {
            console.error('Error marking notification open:', e);
            return false;
        }
    }

    // Mark all notifications as opened
    async function markAllRead() {
        try {
            const resp = await fetch(`${API_BASE}/api/notifications`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'mark_all_read' })
            });
            return resp.ok;
        } catch (e) {
            console.error('Error marking all read:', e);
            return false;
        }
    }

    // Render notification panel with modern design
    function renderNotifications(panel, items) {
        panel.innerHTML = '';

        // Header
        const header = document.createElement('div');
        header.style.cssText = `
            padding: 16px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9fafb;
        `;

        const title = document.createElement('h6');
        title.textContent = 'Notifications';
        title.style.cssText = 'margin: 0; font-weight: 600; font-size: 14px; color: #1f2937;';

        const markAllBtn = document.createElement('button');
        markAllBtn.textContent = 'Mark all read';
        markAllBtn.className = 'btn btn-sm btn-link';
        markAllBtn.style.cssText = `
            padding: 4px 8px;
            font-size: 12px;
            color: #3b82f6;
            text-decoration: none;
            border: none;
            background: none;
            cursor: pointer;
        `;
        markAllBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            markAllBtn.disabled = true;
            markAllBtn.textContent = 'Marking...';
            const ok = await markAllRead();
            markAllBtn.disabled = false;
            markAllBtn.textContent = 'Mark all read';
            if (ok) {
                // Refresh notifications
                const updated = await fetchNotifications();
                renderNotifications(panel, updated);
                updateBadge(updated);
            }
        });

        header.appendChild(title);
        header.appendChild(markAllBtn);
        panel.appendChild(header);

        // Content area
        const content = document.createElement('div');
        content.style.cssText = `
            flex: 1;
            overflow-y: auto;
            max-height: 400px;
        `;

        if (!items || items.length === 0) {
            const empty = document.createElement('div');
            empty.style.cssText = `
                padding: 32px 16px;
                text-align: center;
                color: #9ca3af;
                font-size: 14px;
            `;
            empty.textContent = 'No notifications';
            content.appendChild(empty);
        } else {
            items.forEach((notif) => {
                const item = document.createElement('div');
                item.style.cssText = `
                    padding: 12px 16px;
                    border-bottom: 1px solid #f3f4f6;
                    cursor: pointer;
                    transition: background 0.2s;
                    ${notif.is_open === 0 ? 'background-color: #eff6ff;' : ''}
                `;
                item.addEventListener('mouseover', () => {
                    item.style.backgroundColor = notif.is_open === 0 ? '#dbeafe' : '#f9fafb';
                });
                item.addEventListener('mouseout', () => {
                    item.style.backgroundColor = notif.is_open === 0 ? '#eff6ff' : 'white';
                });

                const timeText = formatTimeAgo(notif.created_at);
                const isUnread = notif.is_open === 0;

                const itemHTML = `
                    <div style="display: flex; gap: 8px;">
                        <div style="flex: 1;">
                            <div style="font-size: 12px; color: #6b7280; margin-bottom: 4px;">
                                ${timeText}
                                ${isUnread ? '<span style="margin-left: 8px; display: inline-block; width: 8px; height: 8px; background: #3b82f6; border-radius: 50%; vertical-align: middle;"></span>' : ''}
                            </div>
                            <div style="font-size: 13px; ${isUnread ? 'font-weight: 600; color: #1f2937;' : 'color: #6b7280;'} line-height: 1.4;">
                                ${notif.remarks || 'No message'}
                            </div>
                        </div>
                    </div>
                `;
                item.innerHTML = itemHTML;

                item.addEventListener('click', async () => {
                    const ok = await markNotificationOpen(notif.id);
                    if (ok) {
                        // Update UI
                        item.style.backgroundColor = 'white';
                        const msgEl = item.querySelector('div div:last-child');
                        if (msgEl) {
                            msgEl.style.fontWeight = '400';
                            msgEl.style.color = '#6b7280';
                        }
                        const blueIcon = item.querySelector('span[style*="background: #3b82f6"]');
                        if (blueIcon) blueIcon.remove();
                        // Update badge
                        const unreadCount = Array.from(content.querySelectorAll('div')).filter(el => {
                            return el.style.backgroundColor && el.style.backgroundColor.includes('eff6ff');
                        }).length;
                        if (notificationCount) {
                            if (unreadCount > 0) {
                                notificationCount.textContent = String(unreadCount - 1);
                                if (unreadCount - 1 === 0) notificationCount.classList.add('d-none');
                            } else {
                                notificationCount.classList.add('d-none');
                            }
                        }
                    }
                });

                content.appendChild(item);
            });
        }

        panel.appendChild(content);

        // Footer
        const footer = document.createElement('div');
        footer.style.cssText = `
            padding: 12px 16px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            background: #f9fafb;
        `;
        const viewAllLink = document.createElement('a');
        viewAllLink.href = '#';
        viewAllLink.textContent = 'View all notifications';
        viewAllLink.style.cssText = `
            font-size: 12px;
            color: #3b82f6;
            text-decoration: none;
        `;
        viewAllLink.addEventListener('click', (e) => {
            e.preventDefault();
            // You can navigate to a full notifications page here
            // window.location.href = '/notifications';
        });
        footer.appendChild(viewAllLink);
        panel.appendChild(footer);
    }

    // Update badge count
    function updateBadge(items) {
        const unreadCount = (items || []).filter(n => n.is_open === 0).length;
        if (notificationCount) {
            if (unreadCount > 0) {
                notificationCount.textContent = String(unreadCount);
                notificationCount.classList.remove('d-none');
            } else {
                notificationCount.classList.add('d-none');
            }
        }
    }

    // Button click handler
    btnNotifications.addEventListener('click', async (ev) => {
        ev.stopPropagation();
        const panel = createNotificationPanel();

        if (panel.classList.contains('d-none')) {
            // Show panel
            const rect = btnNotifications.getBoundingClientRect();
            panel.style.top = (rect.bottom + window.scrollY + 10) + 'px';
            panel.style.left = (rect.left + window.scrollX - 150) + 'px';
            panel.classList.remove('d-none');

            // Fetch and render
            const items = await fetchNotifications();
            renderNotifications(panel, items);
            updateBadge(items);
        } else {
            // Hide panel
            panel.classList.add('d-none');
        }
    });

    // Close panel on outside click
    document.addEventListener('click', (e) => {
        const panel = document.getElementById('notification-panel');
        if (panel && !panel.classList.contains('d-none')) {
            if (!panel.contains(e.target) && e.target !== btnNotifications && !btnNotifications.contains(e.target)) {
                panel.classList.add('d-none');
            }
        }
    });

    // Close panel on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const panel = document.getElementById('notification-panel');
            if (panel) panel.classList.add('d-none');
        }
    });
});

-- Index suggestions for SIPINTAR-TI (apply after reviewing)

-- Ensure users.email is indexed for login lookups
CREATE INDEX IF NOT EXISTS idx_users_email ON users (email);

-- Index for audit logs by user_id and action
CREATE INDEX IF NOT EXISTS idx_audit_logs_user ON audit_logs (user_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_action ON audit_logs (action(32));

-- Index for security events by event_type and user_id
CREATE INDEX IF NOT EXISTS idx_security_events_type ON security_events (event_type(32));
CREATE INDEX IF NOT EXISTS idx_security_events_user ON security_events (user_id);

-- Common lookups for items and categories
CREATE INDEX IF NOT EXISTS idx_items_name ON items (name(64));
CREATE INDEX IF NOT EXISTS idx_categories_name ON categories (name(64));

-- Add more indexes based on slow query log analysis

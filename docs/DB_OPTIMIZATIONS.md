# Database Optimization Recommendations

1. Add indexes for columns used in WHERE and JOIN clauses (see `database/index_suggestions.sql`).
2. Enable MySQL slow query log and analyze queries with `pt-query-digest` or `EXPLAIN`.
3. Normalize columns that store long repeated strings; use foreign keys for categories/items where applicable.
4. Run `ANALYZE TABLE` and `OPTIMIZE TABLE` during maintenance windows.
5. Consider archiving old `audit_logs` and `security_events` into separate tables or partitions.

Apply suggested indexes after reviewing production load and taking backups first.

START TRANSACTION;

INSERT INTO `schedule_recurrence_rules`
    (`id`, `rule_key`, `rule_name`, `is_active`, `sort_order`, `created_at`, `updated_at`)
VALUES
    (1, 'every_day', 'Every day', 1, 1, NOW(), NOW()),
    (2, 'weekdays', 'Weekdays only', 1, 2, NOW(), NOW()),
    (3, 'weekly_day', 'Weekly', 1, 3, NOW(), NOW()),
    (4, 'month_same_day', 'Same day of month', 1, 4, NOW(), NOW()),
    (5, 'month_last_day', 'Last day of month', 1, 5, NOW(), NOW()),
    (6, 'quarter_same_day', 'Same day of quarter', 1, 6, NOW(), NOW()),
    (7, 'quarter_last_day', 'Last day of quarter', 1, 7, NOW(), NOW());

COMMIT;
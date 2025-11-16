-- SQL query to remove is_handover column from working_schedule table in MySQL
-- Run this query in phpMyAdmin SQL tab

ALTER TABLE `working_schedule` 
DROP COLUMN `is_handover`;


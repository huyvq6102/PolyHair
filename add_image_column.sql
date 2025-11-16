-- SQL query to add image column to working_schedule table in MySQL
-- Run this query in phpMyAdmin SQL tab

ALTER TABLE `working_schedule` 
ADD COLUMN `image` VARCHAR(255) NULL AFTER `is_handover`;


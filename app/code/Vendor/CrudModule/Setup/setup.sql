-- Vendor_CrudModule Database Setup
-- Run this SQL script to create the required database table

-- Drop table if exists (optional - uncomment if you need to recreate)
-- DROP TABLE IF EXISTS `vendor_crud_item`;

-- Create vendor_crud_item table
CREATE TABLE IF NOT EXISTS `vendor_crud_item` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Item ID',
  `title` varchar(255) NOT NULL COMMENT 'Item Title',
  `description` text DEFAULT NULL COMMENT 'Item Description',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Status',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Created At',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Updated At',
  PRIMARY KEY (`item_id`),
  KEY `VENDOR_CRUD_ITEM_TITLE` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Vendor CRUD Item Table';

-- Insert sample data (optional)
INSERT INTO `vendor_crud_item` (`title`, `description`, `status`) VALUES
('Sample Item 1', 'This is a sample item description for testing', 1),
('Sample Item 2', 'Another sample item with different content', 1),
('Sample Item 3', 'Third sample item for demonstration', 0),
('Sample Item 4', 'Fourth sample item with longer description text that demonstrates how the grid handles larger content', 1),
('Sample Item 5', 'Final sample item for testing purposes', 1);

-- Verify table creation
SELECT 'Table created successfully!' as status;
SELECT COUNT(*) as total_items FROM `vendor_crud_item`;

-- ================================================================
-- 西游记 + 赌大小 数据库完整初始化脚本
-- 版本: 2.2
-- 更新日期: 2026-04-24
-- 适用环境: MySQL 5.7+ / MySQL 8.0+
-- ================================================================

-- 设置客户端字符集
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- 创建数据库
CREATE DATABASE IF NOT EXISTS `xyj`
DEFAULT CHARACTER SET utf8mb4
DEFAULT COLLATE utf8mb4_unicode_ci;

USE `xyj`;

-- 设置连接字符集
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ================================================================
-- 删除旧表 (如果存在)
-- ================================================================
DROP TABLE IF EXISTS `bet_history`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `position_fees`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `positions`;
DROP TABLE IF EXISTS `kline_data`;
DROP TABLE IF EXISTS `stocks`;
DROP TABLE IF EXISTS `npcs`;
DROP TABLE IF EXISTS `items`;
DROP TABLE IF EXISTS `maps`;
DROP TABLE IF EXISTS `map_npcs`;
DROP TABLE IF EXISTS `map_items`;

-- ================================================================
-- 用户表
-- ================================================================
CREATE TABLE `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `coin` INT NOT NULL DEFAULT 100,
    `silver` INT NOT NULL DEFAULT 100,
    `login_token` VARCHAR(64) DEFAULT NULL,
    `token_expires_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_username` (`username`),
    INDEX `idx_token` (`login_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 游戏记录表 (赌大小)
-- ================================================================
CREATE TABLE `bet_history` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `bet_amount` INT NOT NULL,
    `bet_choice` ENUM('big', 'small') NOT NULL,
    `dice_result` TINYINT UNSIGNED NOT NULL,
    `is_win` TINYINT(1) NOT NULL,
    `win_amount` INT NOT NULL DEFAULT 0,
    `commission` INT NOT NULL DEFAULT 0,
    `coin_after` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_user_created` (`user_id`, `created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 股票表
-- ================================================================
CREATE TABLE `stocks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `symbol` VARCHAR(10) NOT NULL UNIQUE,
    `name` VARCHAR(50) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `previous_price` DECIMAL(10,2) NOT NULL,
    `volatility` DECIMAL(5,4) DEFAULT 0.02,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 持仓表
-- ================================================================
CREATE TABLE `positions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `stock_id` INT NOT NULL,
    `type` ENUM('long','short') NOT NULL,
    `quantity` INT NOT NULL,
    `avg_price` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_position` (`user_id`, `stock_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 交易记录表
-- ================================================================
CREATE TABLE `transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `stock_id` INT NOT NULL,
    `type` ENUM('buy','sell','short','cover') NOT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `total_amount` DECIMAL(15,2) NOT NULL,
    `profit_loss` DECIMAL(15,2) DEFAULT 0,
    `fee` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 持仓费用记录表
-- ================================================================
CREATE TABLE `position_fees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `stock_id` INT NOT NULL,
    `position_type` ENUM('long','short') NOT NULL,
    `position_value` DECIMAL(15,2) NOT NULL,
    `fee_amount` DECIMAL(10,2) NOT NULL,
    `fee_rate` DECIMAL(10,6) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- K线数据表
-- ================================================================
CREATE TABLE `kline_data` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `stock_id` INT NOT NULL,
    `period` VARCHAR(10) NOT NULL,
    `open_price` DECIMAL(10,2) NOT NULL,
    `high_price` DECIMAL(10,2) NOT NULL,
    `low_price` DECIMAL(10,2) NOT NULL,
    `close_price` DECIMAL(10,2) NOT NULL,
    `volume` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_kline` (`stock_id`, `period`, `created_at`),
    FOREIGN KEY (`stock_id`) REFERENCES `stocks`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- NPC表
-- ================================================================
CREATE TABLE `npcs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL COMMENT 'NPC名称',
    `title` VARCHAR(100) DEFAULT NULL COMMENT '称号',
    `description` TEXT COMMENT '描述',
    `race` VARCHAR(20) DEFAULT '人类' COMMENT '种族：人类、妖怪、神仙等',
    `gender` ENUM('男', '女', '未知') DEFAULT '男' COMMENT '性别',
    `age` INT DEFAULT 30 COMMENT '年龄',
    `level` INT DEFAULT 1 COMMENT '等级',
    `hp` INT DEFAULT 100 COMMENT '气血',
    `max_hp` INT DEFAULT 100 COMMENT '最大气血',
    `mp` INT DEFAULT 50 COMMENT '法力',
    `max_mp` INT DEFAULT 50 COMMENT '最大法力',
    `attack` INT DEFAULT 10 COMMENT '攻击',
    `defense` INT DEFAULT 10 COMMENT '防御',
    `dodge` INT DEFAULT 10 COMMENT '躲闪',
    `speed` INT DEFAULT 10 COMMENT '速度',
    `wuxing` VARCHAR(50) DEFAULT '深不可测' COMMENT '武学境界',
    `daohang` VARCHAR(50) DEFAULT '已证大道' COMMENT '道行境界',
    `fali` VARCHAR(50) DEFAULT '法力无边' COMMENT '法力修为',
    `neili` VARCHAR(50) DEFAULT '一甲子' COMMENT '内力修为',
    `appearance` VARCHAR(50) DEFAULT '其貌不扬' COMMENT '容貌',
    `race_looks` VARCHAR(50) DEFAULT '生得稀松平常' COMMENT '长相',
    `hp_status` VARCHAR(50) DEFAULT '气血充盈' COMMENT '气血状态',
    `skill` VARCHAR(100) DEFAULT NULL COMMENT '技能',
    `weapon` VARCHAR(100) DEFAULT NULL COMMENT '武器',
    `armor` VARCHAR(100) DEFAULT NULL COMMENT '防具',
    `can_fight` TINYINT(1) DEFAULT 1 COMMENT '可否战斗',
    `can_trade` TINYINT(1) DEFAULT 1 COMMENT '可否交易',
    `gold` INT DEFAULT 0 COMMENT '携带金币',
    `silver` INT DEFAULT 0 COMMENT '携带银两',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='NPC表';

-- ================================================================
-- 物品表
-- ================================================================
CREATE TABLE `items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL COMMENT '物品名称',
    `type` ENUM('食品', '装备', '武器', '防具', '药品', '材料', '任务物品', '其他') NOT NULL COMMENT '物品类型',
    `sub_type` VARCHAR(50) DEFAULT NULL COMMENT '子类型',
    `description` TEXT COMMENT '物品描述',
    `price` INT DEFAULT 0 COMMENT '价格',
    `attack` INT DEFAULT 0 COMMENT '攻击加成',
    `defense` INT DEFAULT 0 COMMENT '防御加成',
    `dodge` INT DEFAULT 0 COMMENT '躲闪加成',
    `hp` INT DEFAULT 0 COMMENT '气血恢复/加成',
    `mp` INT DEFAULT 0 COMMENT '法力恢复/加成',
    `appearance` INT DEFAULT 0 COMMENT '容貌加成',
    `wuxue` INT DEFAULT 0 COMMENT '武学加成',
    `daohang` INT DEFAULT 0 COMMENT '道行加成',
    `level` INT DEFAULT 1 COMMENT '使用等级要求',
    `stackable` TINYINT(1) DEFAULT 1 COMMENT '可否堆叠',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_name` (`name`),
    INDEX `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='物品表';

-- ================================================================
-- 地图表
-- ================================================================
CREATE TABLE `maps` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL COMMENT '地点名称',
    `description` TEXT COMMENT '地点描述',
    `parent_id` INT DEFAULT NULL COMMENT '父级地图ID',
    `type` ENUM('主城', '野外', '副本', '特殊') DEFAULT '野外' COMMENT '地图类型',
    `level_range` VARCHAR(20) DEFAULT '1-100' COMMENT '等级范围',
    `is_flyable` TINYINT(1) DEFAULT 1 COMMENT '可否飞行',
    `fly_cost` INT DEFAULT 10 COMMENT '飞行费用',
    `npcs` VARCHAR(255) DEFAULT NULL COMMENT 'NPC列表(JSON)',
    `items` VARCHAR(255) DEFAULT NULL COMMENT '物品列表(JSON)',
    `exits` VARCHAR(255) DEFAULT NULL COMMENT '出口(JSON)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='地图表';

-- ================================================================
-- 地图-NPC关联表
-- ================================================================
CREATE TABLE `map_npcs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `map_id` INT NOT NULL,
    `npc_id` INT NOT NULL,
    `respawn_time` INT DEFAULT 300 COMMENT '刷新时间(秒)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`map_id`) REFERENCES `maps`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`npc_id`) REFERENCES `npcs`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_map_npc` (`map_id`, `npc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 地图-物品关联表
-- ================================================================
CREATE TABLE `map_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `map_id` INT NOT NULL,
    `item_id` INT NOT NULL,
    `drop_rate` DECIMAL(5,2) DEFAULT 1.00 COMMENT '掉落概率%',
    `quantity` INT DEFAULT 1 COMMENT '数量',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`map_id`) REFERENCES `maps`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ================================================================
-- 初始化股票数据
-- ================================================================
INSERT INTO `stocks` (`symbol`, `name`, `price`, `previous_price`, `volatility`) VALUES
('SLSJ', '三联书局', 150.00, 148.50, 0.025),
('CASINO', '神秘赌场', 2800.00, 2780.00, 0.020),
('XFH', '喜福会', 300.00, 295.00, 0.022),
('XJQZ', '相记钱庄', 3200.00, 3180.00, 0.028),
('DJDP', '董记当铺', 800.00, 780.00, 0.040),
('DTTG', '大唐天工', 330.00, 325.00, 0.030),
('HCYP', '回春药铺', 220.00, 215.00, 0.035),
('ZXL', '醉星楼', 160.00, 158.00, 0.032),
('NCKZ', '南城客栈', 60.00, 59.00, 0.025),
('YZZ', '御之宅', 75.00, 74.00, 0.030);

-- ================================================================
-- 初始化NPC数据
-- ================================================================
INSERT INTO `npcs` (`name`, `title`, `description`, `race`, `gender`, `age`, `level`, `hp`, `max_hp`, `mp`, `max_mp`, `attack`, `defense`, `dodge`, `speed`, `wuxing`, `daohang`, `fali`, `neili`, `appearance`, `race_looks`, `hp_status`, `weapon`, `armor`, `can_fight`, `can_trade`, `gold`, `silver`) VALUES
('城门士兵', '平民', '长安城防卫士兵，骁勇善战。身穿甲胄，威风凌凌。', '人类', '男', 40, 10, 500, 500, 100, 100, 50, 60, 30, 40, '深不可测', '已证大道', '法力无边', '一甲子', '其貌不扬', '生得稀松平常', '气血充盈', NULL, '甲胄', 1, 1, 100, 50),
('酒店老板', '商人', '热情好客的酒店老板，经营着城里最好的客栈。', '人类', '男', 50, 1, 100, 100, 0, 0, 5, 5, 10, 10, '不堪一击', '心无旁骛', '不会', '二甲子', '其貌不扬', '生得富态', '气血充盈', NULL, NULL, 0, 1, 500, 200),
('铁匠', '工匠', '打造兵器盔甲的老铁匠，手艺精湛。', '人类', '男', 60, 5, 200, 200, 0, 0, 20, 30, 15, 15, '粗通皮毛', '小有所成', '不会', '三甲子', '其貌不扬', '肌肉虬结', '气血充盈', '铁锤', '围裙', 0, 1, 200, 100),
('药店掌柜', '商人', '精通药理的掌柜，售卖各种丹药。', '人类', '女', 35, 3, 150, 150, 50, 50, 10, 15, 20, 25, '粗通皮毛', '初窥门径', '初学乍练', '一甲子', '闭月羞花', '清秀可人', '气血充盈', NULL, NULL, 0, 1, 300, 150),
('乞丐', '流浪者', '衣衫褴褛的乞丐，蹲在墙角乞讨。', '人类', '男', 20, 1, 30, 30, 0, 0, 5, 3, 15, 20, '不堪一击', '不学无术', '不会', '无', '面目全非', '骨瘦如柴', '面黄肌瘦', NULL, '破衣烂衫', 0, 0, 0, 1),
('山贼头目', '恶人', '占山为王的山贼头子，满脸横肉。', '人类', '男', 35, 15, 800, 800, 50, 50, 80, 40, 35, 45, '登堂入室', '已有小成', '粗通法术', '一甲子', '面目狰狞', '虎背熊腰', '气血充盈', '朴刀', '皮甲', 1, 0, 500, 300),
('小妖', '妖怪', '巡山的小妖精，长得奇形怪状。', '妖怪', '男', 100, 5, 300, 300, 50, 50, 25, 20, 30, 35, '粗通皮毛', '初窥门径', '粗通法术', '无', '奇形怪状', '尖嘴猴腮', '气血充盈', '木棍', NULL, 1, 0, 10, 5),
('土地公', '神仙', '掌管一方土地的小神，须发皆白。', '神仙', '男', 500, 20, 1000, 1000, 500, 500, 40, 60, 50, 30, '出神入化', '已有大成', '神通广大', '三甲子', '慈眉善目', '鹤发童颜', '精神矍铄', '拐杖', NULL, 1, 1, 0, 1000),
('观音菩萨', '佛祖', '救苦救难的观世音菩萨，宝相庄严。', '佛祖', '女', 10000, 100, 100000, 100000, 100000, 100000, 1000, 1000, 500, 500, '深不可测', '已证大道', '法力无边', '深不可测', '倾国倾城', '宝相庄严', '圆满无暇', '玉净瓶', '霞衣', 0, 1, 0, 0),
('东海龙王', '神仙', '统领四海的水族之王，威严赫赫。', '龙族', '男', 5000, 50, 50000, 50000, 20000, 20000, 500, 400, 200, 300, '深不可测', '已证大道', '法力无边', '深不可测', '不怒自威', '龙形虎躯', '龙精虎猛', '方天画戟', '龙鳞甲', 1, 1, 0, 10000);

-- ================================================================
-- 初始化物品数据
-- ================================================================
INSERT INTO `items` (`name`, `type`, `sub_type`, `description`, `price`, `attack`, `defense`, `dodge`, `hp`, `mp`, `appearance`, `wuxue`, `daohang`, `level`) VALUES
-- 食品类
('包子', '食品', '食物', '热腾腾的肉包子，香气扑鼻。', 5, 0, 0, 0, 30, 0, 0, 0, 0, 1),
('烤鸡', '食品', '食物', '香喷喷的烤鸡，油光锃亮。', 20, 0, 0, 0, 100, 0, 0, 0, 0, 1),
('人参果', '食品', '仙果', '传说中的人参果，吃一颗可延寿四万七千年。', 10000, 0, 0, 0, 5000, 2000, 50, 1000, 500, 50),
('仙桃', '食品', '仙果', '王母娘娘的蟠桃，仙气缭绕。', 5000, 0, 0, 0, 2000, 1000, 30, 500, 200, 30),
('丹药', '食品', '药品', '恢复气血的丹药。', 50, 0, 0, 0, 200, 0, 0, 0, 0, 5),
('烧酒', '食品', '饮品', '烈性烧酒，喝一口浑身发热。', 10, 0, 0, 0, -10, 0, 0, 0, 0, 1),

-- 装备类
('布衣', '装备', '衣服', '普通的布衣，简朴实用。', 50, 0, 5, 0, 0, 0, 0, 0, 0, 1),
('皮甲', '装备', '护甲', '兽皮制成的护甲，有一定防御。', 200, 0, 15, 0, 10, 0, 0, 0, 0, 5),
('铁甲', '装备', '护甲', '铁片打造的铠甲，防御很高。', 800, 0, 40, -5, 20, 0, 0, 0, 0, 15),
('锁子甲', '装备', '护甲', '铁环相连的铠甲，轻便且坚固。', 1500, 0, 60, 5, 30, 0, 0, 0, 0, 20),
('龙鳞甲', '装备', '护甲', '用龙鳞制成的顶级护甲。', 50000, 0, 150, 10, 100, 50, 20, 500, 200, 50),

-- 武器类
('木棍', '武器', '棍棒', '普通的木棍，聊胜于无。', 10, 5, 0, 0, 0, 0, 0, 0, 0, 1),
('朴刀', '武器', '刀类', '普通士兵常用的刀。', 100, 20, 0, 0, 0, 0, 0, 0, 0, 5),
('铁剑', '武器', '剑类', '铁制长剑，江湖人士常用。', 300, 40, 0, 5, 0, 0, 0, 0, 0, 10),
('青锋剑', '武器', '剑类', '锋利的长剑，寒光闪闪。', 1000, 80, 5, 10, 0, 0, 5, 0, 0, 20),
('方天画戟', '武器', '戟类', '神兵利器，威力无穷。', 50000, 300, 20, 15, 50, 30, 30, 1000, 500, 50),
('金箍棒', '武器', '棍棒', '如意金箍棒，重一万三千五百斤。', 999999, 999, 99, 50, 500, 500, 100, 9999, 9999, 100),

-- 防具类
('草帽', '防具', '头部', '用草编成的帽子，可遮阳挡雨。', 20, 0, 3, 2, 0, 0, 0, 0, 0, 1),
('皮帽', '防具', '头部', '兽皮制成的帽子。', 80, 0, 8, 5, 5, 0, 0, 0, 0, 5),
('凤翅紫金冠', '防具', '头部', '齐天大圣的美猴王冠。', 99999, 0, 50, 30, 100, 100, 50, 1000, 500, 80),

-- 材料类
('铁矿石', '材料', '矿物', '可用于锻造武器的矿石。', 30, 0, 0, 0, 0, 0, 0, 0, 0, 1),
('兽皮', '材料', '皮革', '从野兽身上剥下的皮毛。', 20, 0, 0, 0, 0, 0, 0, 0, 0, 1),
('灵芝', '材料', '草药', '深山老林中采到的灵芝。', 200, 0, 0, 0, 0, 50, 0, 0, 10, 10),
('龙鳞', '材料', '珍稀', '神龙的鳞片，蕴含强大力量。', 10000, 0, 0, 0, 0, 0, 0, 100, 50, 30),

-- 任务物品
('佛经', '任务物品', '书籍', '取经必备的佛经。', 0, 0, 0, 0, 0, 0, 0, 0, 0, 1),
('通关文牒', '任务物品', '文书', '大唐官方颁发的通关文书。', 0, 0, 0, 0, 0, 0, 0, 0, 0, 1);

-- ================================================================
-- 初始化地图数据
-- ================================================================
INSERT INTO `maps` (`name`, `description`, `type`, `level_range`, `is_flyable`, `fly_cost`, `npcs`, `items`) VALUES
-- 第一页地点
('神秘赌场', '一间灯火通明的赌场，里面人声鼎沸，热闹非凡。', '特殊', '10-100', 1, 50, '["酒店老板"]', '["烧酒"]'),
('傲来国', '东胜神州的繁华国度，人民安居乐业，市集热闹非凡。', '主城', '1-20', 1, 10, '["酒店老板", "药店掌柜"]', '["包子", "丹药"]'),
('长安城', '大唐帝国的都城，气势恢宏，街道宽阔，商贾云集。', '主城', '1-30', 1, 10, '["城门士兵", "铁匠", "药店掌柜"]', '["铁甲", "铁剑", "丹药"]'),
('方寸山', '菩提祖师修道之地，云雾缭绕，仙气弥漫。', '野外', '30-50', 1, 20, '["土地公"]', '["灵芝", "人参果"]'),
('高老庄', '高太公家的庄园，猪八戒曾在此入赘。', '野外', '10-30', 1, 15, '["酒店老板"]', '["烤鸡", "烧酒"]'),
('昆仑山', '万山之祖，云海茫茫，有仙家洞府。', '野外', '40-70', 1, 30, '["土地公"]', '["灵芝", "仙桃"]'),
('大雪山', '终年积雪的寒冷之地，环境恶劣。', '野外', '50-80', 1, 35, '["小妖"]', '["皮甲", "皮帽"]'),
('普陀山', '观音菩萨的道场，紫竹林中禅意盎然。', '野外', '20-50', 1, 20, '["观音菩萨"]', '["仙桃", "丹药"]'),
('灌江梅山', '二郎神杨戬的领地，威严肃穆。', '野外', '40-60', 1, 25, '["土地公"]', '["丹药", "灵芝"]'),
('五庄观', '镇元大仙的道观，人参果树名扬天下。', '野外', '30-50', 1, 25, '["土地公"]', '["人参果", "仙桃"]'),
('通天河', '波涛汹涌的大河，需乘船才能渡过。', '野外', '20-40', 1, 15, '["土地公"]', '["丹药"]'),
('蓬莱仙岛', '东海仙山，云雾缥缈，宛如仙境。', '野外', '50-80', 1, 40, '["土地公"]', '["人参果", "仙桃", "灵芝"]'),
('南天门', '天庭入口，金光万道，气势磅礴。', '特殊', '30-60', 1, 30, '["城门士兵"]', '["丹药"]'),
('长安东', '长安城东区，商铺林立，富人区。', '主城', '1-20', 1, 10, '["酒店老板"]', '["烤鸡", "丹药"]'),
('长安南', '长安城南区，平民聚居的地方。', '主城', '1-15', 1, 10, '["乞丐"]', '["包子"]'),
('长安西', '长安城西区，武馆镖局集中地。', '主城', '5-25', 1, 10, '["铁匠"]', '["铁剑", "朴刀", "铁甲"]'),
('火焰山', '熊熊烈火燃烧的山脉，炎热难耐。', '野外', '50-80', 1, 35, '["小妖"]', '["皮甲", "丹药"]'),
('金兜山', '妖怪横行的山脉，危机四伏。', '野外', '30-50', 1, 25, '["山贼头目", "小妖"]', '["朴刀", "皮甲"]'),
('白虎岭', '白骨夫人领地，妖魔猖獗。', '野外', '20-40', 1, 20, '["小妖"]', '["木棍", "布衣"]'),
('乌鸡国', '国王被妖怪害死的国家，暗流涌动。', '野外', '15-35', 1, 15, '["酒店老板"]', '["烤鸡", "丹药"]'),
('车迟国', '三位国师以妖术祸乱朝纲。', '野外', '25-45', 1, 20, '["小妖"]', '["丹药", "灵芝"]'),
('平顶山', '金角银角大王的地盘，法宝众多。', '野外', '35-55', 1, 25, '["小妖"]', '["丹药", "铁剑"]'),
('积雷山', '牛魔王的势力范围，妖怪众多。', '野外', '40-60', 1, 30, '["山贼头目", "小妖"]', '["朴刀", "铁甲"]'),
('碧波潭', '万圣龙公主的巢穴，水族聚集。', '野外', '30-50', 1, 25, '["土地公"]', '["丹药", "灵芝"]'),
('小西天', '黄眉怪假冒的佛祖圣地，佛法不纯。', '野外', '45-65', 1, 35, '["小妖"]', '["铁剑", "丹药"]'),
('天竺', '取经终点站，佛教圣地，佛光普照。', '主城', '50-80', 1, 40, '["土地公"]', '["人参果", "丹药"]'),
('开封府', '大宋都城，包拯坐镇，公正严明。', '主城', '1-30', 1, 15, '["酒店老板", "铁匠"]', '["铁剑", "铁甲"]'),
('宝象国', '唐僧之父曾在此当皇帝，国泰民安。', '主城', '20-40', 1, 20, '["酒店老板"]', '["丹药", "烤鸡"]'),
('祭赛国', '金光塔被盗的冤屈之国。', '野外', '25-45', 1, 20, '["酒店老板"]', '["丹药", "丹药"]'),
('盘丝洞', '蜘蛛精的巢穴，香艳而危险。', '副本', '35-55', 1, 30, '["小妖"]', '["丹药", "灵芝"]'),
('无底洞', '金鼻白毛老鼠精的洞穴，深不见底。', '副本', '40-60', 1, 35, '["小妖"]', '["丹药", "灵芝"]'),

-- 第二页地点
('朱紫国', '国王因惊吓而病的国家，麒麟山有妖怪作祟。', '野外', '30-50', 1, 25, '["酒店老板"]', '["丹药", "烤鸡"]'),
('大雷音寺', '西天取经的终点，如来佛祖的道场。', '特殊', '60-100', 1, 50, '["观音菩萨"]', '["人参果", "佛经"]'),
('蜀山', '剑仙聚集之地，御剑飞行。', '野外', '40-70', 1, 35, '["土地公"]', '["铁剑", "丹药"]'),
('比丘国', '国王被白鹿精迷惑，以小儿心肝为药引。', '野外', '25-45', 1, 20, '["酒店老板"]', '["丹药"]'),
('钦法国', '灭法国，国王曾发誓杀一万个和尚。', '野外', '20-40', 1, 20, '["酒店老板"]', '["丹药", "烤鸡"]'),
('聚见亭', '江湖人士聚会的地方，消息灵通。', '野外', '10-30', 1, 15, '["乞丐"]', '["烧酒"]'),
('玉华县', '三位王子拜孙悟空为师的县城。', '野外', '20-40', 1, 20, '["铁匠"]', '["铁剑", "铁甲"]'),
('金平府', '元霄灯会繁华之地，三只犀牛精作祟。', '野外', '35-55', 1, 30, '["酒店老板"]', '["丹药", "灵芝"]'),
('女儿国', '全国只有女子的国家，喝子母河水怀孕。', '野外', '30-50', 1, 25, '["药店掌柜"]', '["丹药", "仙桃"]'),
('观音寺', '金池长老所在的寺庙，富丽堂皇却藏污纳垢。', '野外', '20-40', 1, 20, '["土地公"]', '["丹药", "布衣"]'),
('枯松林', '阴森恐怖的松林，火云洞在其间。', '野外', '25-45', 1, 20, '["小妖"]', '["木棍", "布衣"]'),
('江州', '唐僧父亲状元陈光蕊的故乡。', '野外', '10-30', 1, 15, '["酒店老板"]', '["包子", "丹药"]'),
('隐雾山', '豹头山有艾叶花皮豹子精。', '野外', '30-50', 1, 25, '["山贼头目"]', '["朴刀", "皮甲"]'),
('竹节山', '九曲盘桓洞有九灵元圣。', '野外', '45-65', 1, 35, '["小妖"]', '["丹药", "灵芝"]'),
('毛颖山', '玉兔精的狡兔三窟。', '野外', '30-50', 1, 25, '["小妖"]', '["丹药"]'),
('黑风洞', '黑熊精的洞穴，偷了袈裟。', '野外', '35-55', 1, 30, '["山贼头目", "小妖"]', '["铁剑", "丹药"]'),
('云栈', '猪八戒的前身高老庄之前的住处。', '野外', '15-35', 1, 15, '["乞丐"]', '["烧酒"]'),
('麒麟山', '赛太岁的金毛犼所在地。', '野外', '35-55', 1, 30, '["小妖"]', '["丹药", "灵芝"]'),
('流沙河', '沙僧的领地，鹅毛都不能浮。', '野外', '20-40', 1, 20, '["土地公"]', '["丹药"]'),
('鹰愁涧', '小白龙吃掉唐僧马的地方。', '野外', '25-45', 1, 20, '["土地公"]', '["丹药", "灵芝"]'),
('毒敌山', '蝎子精的琵琶洞。', '野外', '40-60', 1, 30, '["小妖"]', '["丹药", "灵芝"]'),
('罗汉塔', '十八罗汉修行之地。', '特殊', '50-80', 1, 40, '["土地公"]', '["人参果", "丹药"]'),
('青龙山', '玄英洞有三星犀牛。', '野外', '45-65', 1, 35, '["山贼头目", "小妖"]', '["铁甲", "丹药"]'),
('豹头山', '南山大王豹子精的地盘。', '野外', '30-50', 1, 25, '["山贼头目"]', '["朴刀", "皮甲"]'),
('黄风洞', '黄风怪的三昧神风令人闻风丧胆。', '野外', '30-50', 1, 25, '["小妖"]', '["丹药", "丹药"]'),
('峨嵋山', '普贤菩萨道场，白象精的故乡。', '野外', '40-60', 1, 30, '["土地公"]', '["灵芝", "丹药"]'),
('荆棘岭', '十八公等树精居住的幽静之地。', '野外', '25-45', 1, 20, '["土地公"]', '["灵芝"]'),
('五台山', '文殊菩萨道场，青毛狮子的故乡。', '野外', '40-60', 1, 30, '["土地公"]', '["丹药", "灵芝"]'),
('双叉岭', '取经路上危险重重的山岭。', '野外', '10-30', 1, 15, '["山贼头目"]', '["朴刀", "布衣"]');

-- ================================================================
-- 地图-NPC关联数据
-- ================================================================
INSERT INTO `map_npcs` (`map_id`, `npc_id`, `respawn_time`) VALUES
(1, 2, 60),
(2, 2, 60), (2, 4, 120),
(3, 1, 300), (3, 3, 180), (3, 4, 120),
(4, 7, 300),
(5, 2, 60),
(6, 7, 300),
(9, 8, 600),
(12, 8, 600),
(13, 1, 300);

-- ================================================================
-- 地图-物品关联数据
-- ================================================================
INSERT INTO `map_items` (`map_id`, `item_id`, `drop_rate`, `quantity`) VALUES
(1, 6, 50.00, 1),
(2, 1, 30.00, 1),
(3, 8, 10.00, 1), (3, 13, 15.00, 1), (3, 5, 20.00, 1),
(4, 23, 20.00, 1), (4, 3, 1.00, 1),
(12, 3, 2.00, 1), (12, 4, 5.00, 1);

-- ================================================================
-- 初始化完成
-- ================================================================

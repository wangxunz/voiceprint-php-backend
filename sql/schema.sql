-- sql/schema.sql - 声纹变声系统数据库初始化
-- 使用方法: mysql -u root -p < sql/schema.sql

CREATE DATABASE IF NOT EXISTS voiceprint_converter
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE voiceprint_converter;

-- 声纹表
CREATE TABLE IF NOT EXISTS voiceprints (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    voiceprint_id   VARCHAR(64)     NOT NULL UNIQUE COMMENT '声纹唯一 ID',
    file_path       VARCHAR(500)    NOT NULL COMMENT '录音文件路径',
    file_name       VARCHAR(255)    NOT NULL COMMENT '原始文件名',
    file_size       BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小(字节)',
    duration        INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT '时长(秒)',
    embedding_path  VARCHAR(500)    NULL     COMMENT '声纹特征向量文件路径(.npy)',
    status          ENUM('pending','extracting','ready','failed')
                                    NOT NULL DEFAULT 'pending' COMMENT '状态',
    error_message   TEXT            NULL     COMMENT '错误信息',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='声纹信息';

-- 变声任务表
CREATE TABLE IF NOT EXISTS conversion_tasks (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id             VARCHAR(64)     NOT NULL UNIQUE COMMENT '任务唯一 ID',
    voiceprint_id       VARCHAR(64)     NOT NULL COMMENT '关联声纹 ID',
    song_file_path      VARCHAR(500)    NOT NULL COMMENT '歌曲文件路径',
    song_name           VARCHAR(255)    NOT NULL COMMENT '歌曲名称',
    song_original_name  VARCHAR(255)    NOT NULL COMMENT '原始文件名',
    song_size           BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '歌曲文件大小',
    song_duration       INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT '歌曲时长(秒)',
    pitch_shift         INT             NOT NULL DEFAULT 0 COMMENT '音调偏移(半音)',
    result_path         VARCHAR(500)    NULL     COMMENT '结果文件路径',
    state               ENUM('pending','separating','converting','rendering','completed','failed')
                                        NOT NULL DEFAULT 'pending' COMMENT '任务状态',
    progress            TINYINT UNSIGNED NOT NULL DEFAULT 0 COMMENT '进度 0-100',
    error_message       TEXT            NULL     COMMENT '错误信息',
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_voiceprint (voiceprint_id),
    INDEX idx_state (state),
    INDEX idx_created (created_at),
    CONSTRAINT fk_voiceprint
        FOREIGN KEY (voiceprint_id) REFERENCES voiceprints(voiceprint_id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='变声任务';

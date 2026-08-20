CREATE TABLE IF NOT EXISTS pm_roles (
    id INT NOT NULL AUTO_INCREMENT,
    name VARCHAR(80) NOT NULL,
    nature ENUM('SYSTEM','CUSTOM') NOT NULL DEFAULT 'CUSTOM',
    notes VARCHAR(255) NOT NULL DEFAULT '',
    created_by VARCHAR(20) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL,
    updated_by VARCHAR(20) NOT NULL DEFAULT '',
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pm_role_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pm_template_nodes (
    id INT NOT NULL AUTO_INCREMENT,
    library ENUM('PROJECT','TASK') NOT NULL DEFAULT 'PROJECT',
    node_type ENUM('FOLDER','PROJECT','TASK') NOT NULL,
    parent_id INT NULL,
    code VARCHAR(40) NOT NULL DEFAULT '',
    name VARCHAR(120) NOT NULL,
    wbs VARCHAR(40) NOT NULL DEFAULT '',
    task_type VARCHAR(40) NOT NULL DEFAULT 'NORMAL',
    start_condition ENUM('ANY','ALL','MANUAL') NOT NULL DEFAULT 'MANUAL',
    duration DECIMAL(10,2) NOT NULL DEFAULT 0,
    description TEXT,
    sort_order INT NOT NULL DEFAULT 0,
    created_by VARCHAR(20) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL,
    updated_by VARCHAR(20) NOT NULL DEFAULT '',
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_pm_template_parent (library, parent_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pm_template_assignments (
    id INT NOT NULL AUTO_INCREMENT,
    node_id INT NOT NULL,
    role_id INT NOT NULL,
    user_id VARCHAR(20) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    UNIQUE KEY uq_pm_template_assignment (node_id, role_id, user_id),
    KEY idx_pm_template_assignment_node (node_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pm_template_dependencies (
    task_id INT NOT NULL,
    predecessor_id INT NOT NULL,
    PRIMARY KEY (task_id, predecessor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pm_projects (
    id INT NOT NULL AUTO_INCREMENT,
    template_id INT NULL,
    code VARCHAR(40) NOT NULL,
    name VARCHAR(120) NOT NULL,
    owner_id VARCHAR(20) NOT NULL DEFAULT '',
    status ENUM('TO_BE_START','STARTING','PAUSED','FINISHED') NOT NULL DEFAULT 'TO_BE_START',
    planned_start DATETIME NULL,
    planned_end DATETIME NULL,
    actual_start DATETIME NULL,
    actual_end DATETIME NULL,
    description TEXT,
    created_by VARCHAR(20) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL,
    updated_by VARCHAR(20) NOT NULL DEFAULT '',
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pm_project_code (code),
    KEY idx_pm_project_status (status),
    KEY idx_pm_project_owner (owner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pm_project_roles (
    id INT NOT NULL AUTO_INCREMENT,
    project_id INT NOT NULL,
    role_id INT NOT NULL,
    user_id VARCHAR(20) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pm_project_role (project_id, role_id, user_id),
    KEY idx_pm_project_role_project (project_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pm_tasks (
    id INT NOT NULL AUTO_INCREMENT,
    project_id INT NOT NULL,
    template_id INT NULL,
    parent_id INT NULL,
    code VARCHAR(40) NOT NULL,
    name VARCHAR(120) NOT NULL,
    wbs VARCHAR(40) NOT NULL DEFAULT '',
    task_type VARCHAR(40) NOT NULL DEFAULT 'NORMAL',
    start_condition ENUM('ANY','ALL','MANUAL') NOT NULL DEFAULT 'MANUAL',
    status ENUM('TO_BE_START','STARTING','FINISHED') NOT NULL DEFAULT 'TO_BE_START',
    planned_duration DECIMAL(10,2) NOT NULL DEFAULT 0,
    planned_start DATETIME NULL,
    planned_end DATETIME NULL,
    actual_start DATETIME NULL,
    actual_end DATETIME NULL,
    description TEXT,
    sort_order INT NOT NULL DEFAULT 0,
    created_by VARCHAR(20) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL,
    updated_by VARCHAR(20) NOT NULL DEFAULT '',
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pm_task_code (project_id, code),
    KEY idx_pm_task_project (project_id, parent_id, sort_order),
    KEY idx_pm_task_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pm_task_assignments (
    id INT NOT NULL AUTO_INCREMENT,
    task_id INT NOT NULL,
    role_id INT NOT NULL,
    user_id VARCHAR(20) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pm_task_assignment (task_id, role_id, user_id),
    KEY idx_pm_task_assignment_task (task_id),
    KEY idx_pm_task_assignment_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pm_task_dependencies (
    task_id INT NOT NULL,
    predecessor_id INT NOT NULL,
    PRIMARY KEY (task_id, predecessor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pm_task_io (
    id INT NOT NULL AUTO_INCREMENT,
    task_id INT NOT NULL,
    direction ENUM('INPUT','OUTPUT') NOT NULL,
    name VARCHAR(120) NOT NULL,
    reference VARCHAR(500) NOT NULL DEFAULT '',
    notes VARCHAR(255) NOT NULL DEFAULT '',
    created_by VARCHAR(20) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_pm_task_io (task_id, direction)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pm_logs (
    id BIGINT NOT NULL AUTO_INCREMENT,
    project_id INT NULL,
    task_id INT NULL,
    action VARCHAR(40) NOT NULL,
    detail VARCHAR(500) NOT NULL DEFAULT '',
    user_id VARCHAR(20) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    KEY idx_pm_log_project (project_id, created_at),
    KEY idx_pm_log_task (task_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS pm_settings (
    setting_key VARCHAR(60) NOT NULL,
    setting_value VARCHAR(255) NOT NULL DEFAULT '',
    updated_by VARCHAR(20) NOT NULL DEFAULT '',
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO pm_roles
    (id, name, nature, notes, created_by, created_at, updated_by, updated_at)
VALUES
    (1, '项目负责人', 'SYSTEM', '项目的主要负责人', 'system', NOW(), 'system', NOW());

INSERT IGNORE INTO pm_settings
    (setting_key, setting_value, updated_by, updated_at)
VALUES
    ('work_calendar', 'SINGLE_REST', 'system', NOW());

INSERT INTO scripts(script, pagesecurity, description, creation_date, created_by, function_name)
SELECT 'ProjectManagement.php', 1, '项目管理', UNIX_TIMESTAMP(), 'system', '项目管理'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM scripts WHERE script='ProjectManagement.php');

UPDATE scripts SET pagesecurity=0, description='项目管理', function_name='项目管理'
WHERE script='ProjectManagement.php';

INSERT INTO user_power(user_id,function_name,model_name,use_flag,creation_date,created_by)
SELECT u.userid,'项目管理','项目管理','1',UNIX_TIMESTAMP(),'system'
FROM www_users u
WHERE NOT EXISTS (
    SELECT 1 FROM user_power p WHERE p.user_id=u.userid AND p.function_name='项目管理'
);

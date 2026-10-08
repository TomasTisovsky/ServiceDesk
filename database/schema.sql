CREATE TABLE users (
                       id NUMBER(10) GENERATED ALWAYS AS IDENTITY,
                       name VARCHAR2(100 CHAR) NOT NULL,
                       email VARCHAR2(254 CHAR) NOT NULL,
                       password_hash VARCHAR2(255 CHAR) NOT NULL,
                       role VARCHAR2(20 CHAR) NOT NULL,

                       CONSTRAINT pk_users PRIMARY KEY (id),
                       CONSTRAINT uq_users_email UNIQUE (email),
                       CONSTRAINT ck_users_role
                           CHECK (role IN ('EMPLOYEE', 'TECHNICIAN'))
);


CREATE TABLE devices (
                         id NUMBER(10) GENERATED ALWAYS AS IDENTITY,
                         name VARCHAR2(150 CHAR) NOT NULL,
                         description VARCHAR2(1000 CHAR),
                         assigned_user_id NUMBER(10),

                         CONSTRAINT pk_devices PRIMARY KEY (id),
                         CONSTRAINT fk_devices_assigned_user
                             FOREIGN KEY (assigned_user_id) REFERENCES users (id)
);


CREATE TABLE tickets (
                         id NUMBER(10) GENERATED ALWAYS AS IDENTITY,
                         title VARCHAR2(200 CHAR) NOT NULL,
                         description CLOB NOT NULL,
                         author_id NUMBER(10) NOT NULL,
                         technician_id NUMBER(10),
                         device_id NUMBER(10) NOT NULL,
                         priority VARCHAR2(10 CHAR) DEFAULT 'MEDIUM' NOT NULL,
                         status VARCHAR2(20 CHAR) DEFAULT 'NEW' NOT NULL,
                         created_at TIMESTAMP(6) DEFAULT SYS_EXTRACT_UTC(SYSTIMESTAMP) NOT NULL,
                         resolution CLOB,
                         resolved_at TIMESTAMP(6),

                         CONSTRAINT pk_tickets PRIMARY KEY (id),

                         CONSTRAINT fk_tickets_author
                             FOREIGN KEY (author_id) REFERENCES users (id),

                         CONSTRAINT fk_tickets_technician
                             FOREIGN KEY (technician_id) REFERENCES users (id),

                         CONSTRAINT fk_tickets_device
                             FOREIGN KEY (device_id) REFERENCES devices (id),

                         CONSTRAINT ck_tickets_priority
                             CHECK (priority IN ('LOW', 'MEDIUM', 'HIGH')),

                         CONSTRAINT ck_tickets_status
                             CHECK (status IN ('NEW', 'IN_PROGRESS', 'RESOLVED')),

                         CONSTRAINT ck_tickets_state
                             CHECK (
                                 (
                                     status = 'NEW'
                                         AND technician_id IS NULL
                                         AND resolved_at IS NULL
                                     )
                                     OR
                                 (
                                     status = 'IN_PROGRESS'
                                         AND technician_id IS NOT NULL
                                         AND resolved_at IS NULL
                                     )
                                     OR
                                 (
                                     status = 'RESOLVED'
                                         AND technician_id IS NOT NULL
                                         AND resolved_at IS NOT NULL
                                     )
                                 ),

                         CONSTRAINT ck_tickets_resolved_time
                             CHECK (resolved_at IS NULL OR resolved_at >= created_at)
);


CREATE TABLE ticket_comments (
                                 id NUMBER(10) GENERATED ALWAYS AS IDENTITY,
                                 ticket_id NUMBER(10) NOT NULL,
                                 author_id NUMBER(10) NOT NULL,
                                 content CLOB NOT NULL,
                                 created_at TIMESTAMP(6)
                                     DEFAULT SYS_EXTRACT_UTC(SYSTIMESTAMP) NOT NULL,

                                 CONSTRAINT pk_ticket_comments PRIMARY KEY (id),

                                 CONSTRAINT fk_comments_ticket
                                     FOREIGN KEY (ticket_id) REFERENCES tickets (id),

                                 CONSTRAINT fk_comments_author
                                     FOREIGN KEY (author_id) REFERENCES users (id)
);


CREATE INDEX ix_devices_assigned_user
    ON devices (assigned_user_id);

CREATE INDEX ix_tickets_author
    ON tickets (author_id);

CREATE INDEX ix_tickets_technician
    ON tickets (technician_id);

CREATE INDEX ix_tickets_device
    ON tickets (device_id);

CREATE INDEX ix_tickets_status_priority
    ON tickets (status, priority);

CREATE INDEX ix_comments_ticket_time
    ON ticket_comments (ticket_id, created_at, id);

CREATE INDEX ix_comments_author
    ON ticket_comments (author_id);
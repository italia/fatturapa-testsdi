CREATE TABLE notifications (
    uuid integer NOT NULL,
    invoice_uuid integer,
    type character(1),
    state character(1),
    blob bytea
);

ALTER TABLE ONLY notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (uuid);

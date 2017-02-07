CREATE TABLE addressbooks_arcstores (
    addressbookid INTEGER UNSIGNED NOT NULL PRIMARY KEY,
    storename VARCHAR(8),
    FOREIGN KEY (addressbookid) REFERENCES addressbooks(id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sources (
    sourceid INTEGER UNSIGNED NOT NULL PRIMARY KEY,
    url VARCHAR(511),
    refresh_interval INTEGER,
    last_accessed INTEGER,
    addressbookid INTEGER UNSIGNED NOT NULL
)

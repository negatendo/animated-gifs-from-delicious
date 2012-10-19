create table items (
    id int(255) not null auto_increment,
    title varchar(255) not null,
    link varchar(255) not null,
    description varchar(255) not null,
    dc_creator varchar(255) not null,
    dc_date datetime not null,
    dc_subject varchar(255) not null,
    file_name varchar(255) not null,
    file_hash varchar(255) not null,
    primary key (id),
    index items_file_name (file_name),
    index items_file_hash (file_hash),
    index items_dc_creator (dc_creator)
);

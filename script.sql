create or replace table securebooksellingdb.book
(
    id    int          not null
        primary key,
    title varchar(128) null,
    constraint id
        unique (id)
);

create or replace table securebooksellingdb.client
(
    id       int          not null
        primary key,
    username varchar(128) not null,
    password varchar(128) not null,
    constraint id
        unique (id),
    constraint username
        unique (username)
);

create or replace table securebooksellingdb.purchase
(
    buyer int not null,
    book  int not null,
    constraint buyer
        unique (buyer, book),
    constraint buyer_3
        unique (buyer),
    constraint purchase_ibfk_1
        foreign key (buyer) references securebooksellingdb.client (id),
    constraint purchase_ibfk_2
        foreign key (book) references securebooksellingdb.book (id)
);

create or replace index book
    on securebooksellingdb.purchase (book);

create or replace index buyer_2
    on securebooksellingdb.purchase (buyer);




drop table if exists aaa;
drop table if exists bbb;
drop table if exists xxx;

create table aaa (
  id int not null primary key auto_increment,
  name varchar (255)
);

create table bbb (
  id int not null primary key auto_increment,
  name varchar (255)
);

create table xxx (
  id int not null primary key auto_increment,
  no int,
  name varchar (255) not null,
  memo varchar (255),
  date date,
  time time
);

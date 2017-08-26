
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
  name varchar (255)
);

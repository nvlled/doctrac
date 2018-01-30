
create table Agents (
	int integer primary key,
	officeId integer primary key,
	fullname varchar(255),
);

create table Campuses (
	int integer primary key,
	name varchar(1024)
);

create table Offices (
	int integer primary key,
	campusId integer,
	name varchar(1024)
);

create table Documents (
	trackingId varchar(255) primary key,
	userId integer,
	attachmentFilename text,
);

create table Dispatches (
	id integer autoincrement primary key,
	trackingId integer,
	timeSent   datetime,
	timeRecv   datetime,
	timeSeen   datetime,
	srcOffice  integer,
	destOffice integer,
);



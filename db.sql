
delete from campuses;
delete from offices;
delete from positions;
delete from privileges;
delete from users;
delete from documents;
delete from document_routes;

set @t = NOW();

insert into campuses(id, code, name, created_at, updated_at) values
(1,  "ala", "Alaminos", @t, @t),
(2,  "asi", "Asingan", @t, @t),
(3,  "bay", "Bayambang", @t, @t),
(4,  "bin", "Binmaley", @t, @t),
(5,  "inf", "Infanta", @t, @t),
(6,  "lin", "Lingayen", @t, @t),
(7,  "car", "San Carlos", @t, @t),
(8,  "sta", "Sta Maria", @t, @t),
(9, "urd",  "Urdaneta", @t, @t);

insert into offices(id, campusId, gateway, name, created_at, updated_at) values
(1,        1, 1, 'Records', @t, @t),
(2,        1, 0, 'MIS', @t, @t),
(3,        1, 0, 'Registrar', @t, @t),
(4,        2, 1, 'Records', @t, @t),
(5,        2, 0, 'MIS', @t, @t),
(6,        2, 0, 'Registrar', @t, @t),
(7,        3, 1, 'Records', @t, @t),
(8,        3, 0, 'MIS', @t, @t),
(9,        3, 0, 'Registrar', @t, @t),
(10,       4, 1, 'Records', @t, @t),
(11,       4, 0, 'MIS', @t, @t),
(12,       4, 0, 'Registrar', @t, @t),
(13,       5, 1, 'Records', @t, @t),
(14,       5, 0, 'MIS', @t, @t),
(15,       5, 0, 'Registrar', @t, @t),
(16,       6, 1, 'Records', @t, @t),
(17,       6, 0, 'MIS', @t, @t),
(18,       6, 0, 'Registrar', @t, @t),
(19,       7, 1, 'Records', @t, @t),
(20,       7, 0, 'MIS', @t, @t),
(21,       7, 0, 'Registrar', @t, @t),
(22,       8, 1, 'Records', @t, @t),
(23,       8, 0, 'MIS', @t, @t),
(24,       8, 0, 'Registrar', @t, @t),
(@urec:=25,9, 1, 'Records', @t, @t),
(@umis:=26,9, 0, 'MIS', @t, @t),
(@uacc:=27,9, 0, 'Accounting', @t, @t),
(@ucoc:=28,9, 0, 'COC', @t, @t),
(@ucsh:=29,9, 0, 'Cashier', @t, @t);

insert into positions(id, name, created_at, updated_at) values
(@head := 1,  'Head', @t, @t),
(@asst := 2,  'Assistant', @t, @t),
(@fact := 3,  'Faculty', @t, @t),
(@clrk := 4,  'Clerk', @t, @t);

insert into privileges(id, name, created_at, updated_at) values
(@admin   := 1,  'admin', @t, @t),
(@officer := 2,  'record officer', @t, @t),
(@agent   := 3,  'agent', @t, @t);


insert into documents
(
    id,
    title,
    details,
    trackingId,
    userId,
    type
) values
(1, "Document A", "AA AAAA AAA", @trackID1:="0001", @off1, 'serial'),
(2, "Document B", "BB BBBB BBB", @trackID2:="0002", @off2, 'serial'),
(3, "Document C", "CC CCCC CCC", @trackID3:="0003", @off2, 'serial'),
(4, "Document D", "DD DDDD DDD", @trackID4:="0004", @off1, 'serial'),
(5, "Document E", "EE EEEE EEE", @trackID5:="0005", @off2, 'parallel');


set @day1 = '2017-01-01';
set @day2 = '2017-01-02';
set @day3 = '2017-01-03';
set @day4 = '2017-01-04';
set @_    = NULL;

insert into document_routes
(
    id, pathId, trackingId, officeId, receiverId, senderId,
    prevId, nextId, arrivalTime, forwardTime, final, annotations
) values

--                      recvr  sender  P   N    arrv   forw
(1, 1, @trackID1, @urec, @off1, @off1,  @_, 2,   @day1, @day1, false, @_),
(2, 1, @trackID1, @umis, @_,    @_,     1, @_,   @_,    @_,    true,  @_),

(3, 2, @trackID2, @umis, @off2, @off2,  @_, 4,   @day1, @day1, false, @_),
(4, 2, @trackID2, @ucsh, 3,     @_,     3,  5,   @day2, @_,    false, 'something'),
(5, 2, @trackID2, @uacc, @_,    @_,     4,  6,   @_,    @_,    false, @_),
(6, 2, @trackID2, @ucoc, @_,    @_,     5,  @_,  @_,    @_,    true,  @_),

(23, 7, @trackID3, @umis, @off2, @off2, @_, 24,  @day1, @day1, false, @_),
(24, 7, @trackID3, @ucsh, 3,     4,     23, 25,  @day2, @day2, false, 'something'),
(25, 7, @trackID3, @uacc, @_,    @_,    24, 26,  @_,    @_,    false, @_),
(26, 7, @trackID3, @ucoc, @_,    @_,    25, @_,  @_,    @_,    true,  @_),

(7,  3, @trackID4, @ucoc, @off1, @off1, @_, 8,   @day1, @day1, false, @_),
(8,  3, @trackID4, @ucsh, 5,     5,     7,  9,   @day2, @day2, false, 'sign'),
(9,  3, @trackID4, @umis, 6,     @_,    8,  @_,  @day3, @_,    true, 'blah'),

(11, 4, @trackID5, @urec, @off2, @off2, @_, 14,  @day1, @day2, false, @_),
(12, 5, @trackID5, @urec, @off2, @off2, @_, 15,  @day1, @day2, false, @_),
(13, 6, @trackID5, @urec, @off2, @off2, @_, 16,  @day1, @day2, false,  @_),

(14, 4, @trackID5, @ucoc, 3,     @_,    11, @_,  @_,    @_,    true, @_),
(15, 5, @trackID5, @ucsh, 4,     @_,    12, @_,  @_,    @_,    true, @_),
(16, 6, @trackID5, @umis, 5,     @_,    13, @_,  @_,    @_,    true,  @_);




delete from offices;
delete from positions;
delete from privileges;
delete from users;
delete from documents;
delete from document_routes;

insert into offices(id, campus, name, created_at, updated_at) values
(@rec:=1,    'urdaneta', 'records', NOW(), NOW()),
(@mis:=2,  'urdaneta', 'MIS', NOW(), NOW()),
(@acc:=3,  'urdaneta', 'accounting', NOW(), NOW()),
(@coc:=4,  'urdaneta', 'COC', NOW(), NOW()),
(@csh:=5,  'urdaneta', 'cashier', NOW(), NOW()),
(6,  'lingayen', 'MIS', NOW(), NOW()),
(7,  'lingayen', 'accounting', NOW(), NOW()),
(8,  'lingayen', 'COC', NOW(), NOW()),
(9,  'asingan', 'registrar', NOW(), NOW()),
(10, 'asingan', 'MIS', NOW(), NOW()),
(11, 'asingan', 'accounting', NOW(), NOW()),
(12, 'asingan', 'COC', NOW(), NOW());

insert into positions(id, name, created_at, updated_at) values
(@head := 1,  'Head', NOW(), NOW()),
(@asst := 2,  'Assistant', NOW(), NOW()),
(@fact := 3,  'Faculty', NOW(), NOW()),
(@clrk := 4,  'Clerk', NOW(), NOW());

insert into privileges(id, name, created_at, updated_at) values
(@admin   := 1,  'admin', NOW(), NOW()),
(@officer := 2,  'record officer', NOW(), NOW()),
(@agent   := 3,  'agent', NOW(), NOW());

set @t = NOW();

-- password == bcrypt('x')
set @p = "$2y$10$eW9b3pHETP.xhdww1nUare66H39WqlQW6rLS8gGvH7OK3IN6ji66.";
insert into users
(
    created_at, 
    updated_at,
    id, 
    email,
    password,
    firstname, 
    middlename, 
    lastname, 
    positionId,
    privilegeId,
    officeId
) values
(@t, @t, 1,        "a@x.y", @p, "Astaroth", "Cosette", "Aida", @head, @admin, 1),
(@t, @t, @off1:=2, "b@x.y", @p, "Rohan", "Othello", "Zuleika", @asst, @officer, 2),
(@t, @t, @off2:=3, "c@x.y", @p, "Igerna", "Aramis", "Gandalf", @clrk, @officer, 3),
(@t, @t, 4,        "d@x.y", @p, "Ruslan", "Guenevere", "Mehrab", @clrk, @agent, 3),
(@t, @t, 5,        "e@x.y", @p, "Bedwyr", "Daenerys", "Medraut", @fact, @agent, 2),
(@t, @t, 6,        "f@x.y", @p, "Enobarbus", "Merlin", "Malvina", @asst, @agent, 6),
(@t, @t, 7,        "g@x.y", @p, "Ossian", "Bayard", "Lalage", @head, @agent, 5),
(@t, @t, 8,        "h@x.y", @p, "Morgen", "Cyrano", "Turin", @fact, @agent, 1);

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
(1, 1, @trackID1, @rec, @off1, @off1,  @_, 2,   @day1, @day1, false, @_),
(2, 1, @trackID1, @mis, @_,    @_,     1, @_,   @_,    @_,    true,  @_),

(3, 2, @trackID2, @mis, @off2, @off2,  @_, 4,   @day1, @day1, false, @_),
(4, 2, @trackID2, @csh, 3,     @_,     3,  5,   @day2, @_,    false, 'something'),
(5, 2, @trackID2, @acc, @_,    @_,     4,  6,   @_,    @_,    false, @_),
(6, 2, @trackID2, @coc, @_,    @_,     5,  @_,  @_,    @_,    true,  @_),

(23, 7, @trackID3, @mis, @off2, @off2, @_, 24,  @day1, @day1, false, @_),
(24, 7, @trackID3, @csh, 3,     4,     23, 25,  @day2, @day2, false, 'something'),
(25, 7, @trackID3, @acc, @_,    @_,    24, 26,  @_,    @_,    false, @_),
(26, 7, @trackID3, @coc, @_,    @_,    25, @_,  @_,    @_,    true,  @_),

(7,  3, @trackID4, @coc, @off1, @off1, @_, 8,   @day1, @day1, false, @_),
(8,  3, @trackID4, @csh, 5,     5,     7,  9,   @day2, @day2, false, 'sign'),
(9,  3, @trackID4, @mis, 6,     @_,    8,  @_,  @day3, @_,    true, 'blah'),

(11, 4, @trackID5, @rec, @off2, @off2, @_, 14,  @day1, @day2, false, @_),
(12, 5, @trackID5, @rec, @off2, @off2, @_, 15,  @day1, @day2, false, @_),
(13, 6, @trackID5, @rec, @off2, @off2, @_, 16,  @day1, @day2, false,  @_),

(14, 4, @trackID5, @coc, 3,     @_,    11, @_,  @_,    @_,    true, @_),
(15, 5, @trackID5, @csh, 4,     @_,    12, @_,  @_,    @_,    true, @_),
(16, 6, @trackID5, @mis, 5,     @_,    13, @_,  @_,    @_,    true,  @_);





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

insert into users
(
    created_at, 
    updated_at,
    id, 
    firstname, 
    middlename, 
    lastname, 
    positionId,
    privilegeId,
    officeId
) values
(NOW(), NOW(), 1, "Astaroth", "Cosette", "Aida", @head, @admin, 1),
(NOW(), NOW(), 
    @off1:=2, "Rohan", "Othello", "Zuleika", @asst, @officer, 2),
(NOW(), NOW(), 
    @off2:=3, "Igerna", "Aramis", "Gandalf", @clrk, @officer, 3),
(NOW(), NOW(), 4, "Ruslan", "Guenevere", "Mehrab", @clrk, @agent, 3),
(NOW(), NOW(), 5, "Bedwyr", "Daenerys", "Medraut", @fact, @agent, 2),
(NOW(), NOW(), 6, "Enobarbus", "Merlin", "Malvina", @asst, @agent, 6),
(NOW(), NOW(), 7, "Ossian", "Bayard", "Lalage", @head, @agent, 5),
(NOW(), NOW(), 8, "Morgen", "Cyrano", "Turin", @fact, @agent, 1);

insert into documents
(
    id,
    title,
    details,
    trackingId,
    userId
) values
(1, "Document A", "AA AAAA AAA", @trackID1:="00-12-333", @off1),
(2, "Document B", "BB BBBB BBB", @trackID2:="73-12-216", @off2),
(3, "Document C", "CC CCCC CCC", @trackID3:="12-32-456", @off2),
(4, "Document D", "DD DDDD DDD", @trackID4:="77-31-989", @off1),
(5, "Document E", "EE EEEE EEE", @trackID5:="21-54-449", @off2);


set @day1 = '2017-01-01';
set @day2 = '2017-01-02';
set @day3 = '2017-01-03';
set @day4 = '2017-01-04';
set @_    = NULL;

insert into document_routes
(
    id, pathId, trackingId, officeId, receiverId, senderId,
    prevId, nextId, arrivalTime, final, annotations
) values

(1, 1, @trackID1, @rec, @off1, @off1, @_, 2, @day1, false, @_),
(2, 1, @trackID1, @mis, @_, @_,       1, @_,  @_,   true,  @_),

(3, 2, @trackID2, @mis, @off2, @off2, @_, 4,  @day1, false, @_),
(4, 2, @trackID2, @csh, 3,  @_,       3,  5,  @day2, false, 'something'),
(5, 2, @trackID2, @acc, @_, @_,       4,  6,  @_,    false, @_),
(6, 2, @trackID2, @coc, @_, @_,       5,  @_, @_,    true,  @_),

(23, 7, @trackID3, @mis, @off2, @off2, @_,  24,  @day1, false, @_),
(24, 7, @trackID3, @csh, 3,  4,        23,  25,  @day2, false, 'something'),
(25, 7, @trackID3, @acc, @_, @_,       24,  26,  @_,    false, @_),
(26, 7, @trackID3, @coc, @_, @_,       25,  @_, @_,    true,  @_),

(7,  3, @trackID4, @coc, @off1, @off1, @_, 8,  @day1,  false, @_),
(8,  3, @trackID4, @csh, 5, 5,         7,  9,  @day2, false, 'sign'),
(9,  3, @trackID4, @mis, 6, @_,        8,  @_, @day3, true, 'blah'),

(11,  4, @trackID5, @rec, @off2, @off2, @_, 14, @day1,  false, @_),
(12,  5, @trackID5, @rec, @off2, @off2, @_, 15, @day1,  false, @_),
(13,  6, @trackID5, @rec, @off2, @off2, @_, 16, @day1,  true,  @_),

(14,  4, @trackID5, @coc, 3, @_,        11, @_,  @_,    false, @_),
(15,  5, @trackID5, @csh, 4, @_,        12, @_,  @_,    false, @_),
(16,  6, @trackID5, @mis, 5, @_,        13, @_,  @day2, true,  @_);




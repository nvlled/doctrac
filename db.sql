
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
    @officer1:=2, "Rohan", "Othello", "Zuleika", @asst, @officer, 2),
(NOW(), NOW(), 
    @officer2:=3, "Igerna", "Aramis", "Gandalf", @clrk, @officer, 3),
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
(1, "Document A", "AA AAAA AAA", @trackID1:="00-12-333", @officer1),
(2, "Document B", "BB BBBB BBB", @trackID2:="73-12-216", @officer2),
(3, "Document C", "CC CCCC CCC", @trackID3:="12-32-456", @officer2),
(4, "Document D", "DD DDDD DDD", @trackID4:="77-31-989", @officer1),
(5, "Document E", "EE EEEE EEE", @trackID5:="21-54-449", @officer2);

insert into document_routes
(
    id,
    pathId,
    trackingId,
    officeId,
    userId,
    nextId,
    prevId,
    arrivalTime,
    annotations,
    final
) values

(1, 1, @trackID1, @rec, @officer1, 2, NULL, '2017-01-01', NULL, false),
(2, 1, @trackID1, @mis, NULL,   NULL, 1, NULL, NULL, true),

(3, 2, @trackID2, @mis, @officer2, 4, NULL, '2017-01-01', NULL, false),
(4, 2, @trackID2, @csh, 3,         5, 3,    '2017-01-02', 'do something', false),
(5, 2, @trackID2, @acc, NULL,      6, 4,    NULL, NULL, false),
(6, 2, @trackID2, @coc, NULL,   NULL, 5,    NULL, NULL, true),

(7,  3, @trackID3, @coc, @officer1, 8, NULL, '2017-01-01', NULL,    false),
(8,  3, @trackID3, @csh, 5,         9, 7,    '2017-01-02', 'sign ', false),
(9,  3, @trackID3, @mis, 6,      NULL, 8,    '2017-01-03', 'blah',  true),

(11,  4, @trackID4, @rec, @officer2, 14, NULL, '2017-01-01', NULL, false),
(12,  5, @trackID4, @rec, @officer2, 15, NULL, '2017-01-01', NULL, false),
(13,  6, @trackID4, @rec, @officer2, 16, NULL, '2017-01-01', NULL, true),

(14,  4, @trackID4, @coc, @officer2, NULL, 11,  NULL,        NULL, false),
(15,  5, @trackID4, @csh, @officer2, NULL, 12,  NULL,        NULL, false),
(16,  6, @trackID4, @mis, @officer2, NULL, 13, '2017-01-02', NULL, true);



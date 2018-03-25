<?php

namespace Tests\Feature;

use Tests\TestCase;
//use Illuminate\Foundation\Testing\WithFaker;
//use Illuminate\Foundation\Testing\RefreshDatabase;

use App\User;
use App\Office;
use App\DoctracAPI;
use App\ArrayObject;

class RoutingTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        print "AAAAAAAAAA";
        $officeA = new \App\Office();

        $this->assertTrue(true);
    }

    public function testParallel() {
        self::createUserOffices();

        $officeXA = Office::withUserName("X-A");
        $officeXB = Office::withUserName("X-B");
        $officeXC = Office::withUserName("X-C");

        $officeYA = Office::withUserName("Y-A");
        $officeYB = Office::withUserName("Y-B");
        $officeYC = Office::withUserName("Y-C");
        $officeYD = Office::withUserName("Y-D");

        $officeZA = Office::withUserName("Z-A");
        $officeZB = Office::withUserName("Z-B");
        $officeZC = Office::withUserName("Z-C");
        $officeZD = Office::withUserName("Z-D");

        $api = new DoctracAPI($officeXA->user);
        $doc = $api->dispatchDocument([
            "title"=>str_random(),
            "officeIds"=>[
                $officeYA->id,
                $officeZA->id,
            ],
            "type"=>"parallel",
        ]);

        $this->assertEquals("delivering", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-A"));
        $this->assertFalse($api->hasErrors());

        $api->receiveFromOffice($doc, "Y-A");
        $this->assertFalse($api->hasErrors());
        $api->receiveFromOffice($doc, "Z-A");
        $this->assertFalse($api->hasErrors());

        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-A"));



        $doc = $api->dispatchDocument([
            "title"=>str_random(),
            "officeIds"=>[
                $officeYA->id,
                $officeZA->id,
            ],
            "type"=>"parallel",
        ]);

        $api->receiveFromOffice($doc, "Y-A");
        //$api->forwardToOffices($doc, ["Y-B", "Y-C", "Y-D"]); TODO
        $api->forwardDocument([
            "document"=>$doc,
            "office"  =>"Y-A",
            "type"    =>"parallel",
            "officeIds"=>[
                $officeYB->id,
                $officeYC->id,
                $officeYD->id,
            ],
        ]);

        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-B"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-C"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-D"));

        //$api->forwardToOffice($doc, "Y-B");
        //$api->receiveFromOffice($doc, "Y-B");

        $this->assertEquals("delivering", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-A"));

        $api->receiveFromOffice($doc, "Z-A");
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-A"));

        $api->receiveFromOffice($doc, "Y-B");
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-B"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-C"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-D"));

        $api->receiveFromOffice($doc, "Y-C");
        $api->receiveFromOffice($doc, "Y-D");

        $this->assertEquals("processing", $api->routeStatus($doc, "Y-B"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-C"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-D"));

        $api->forwardDocument([
            "document"=>$doc,
            "office"  =>"Z-A",
            "type"    =>"parallel",
            "officeIds"=>[
                $officeZB->id,
                $officeZC->id,
                $officeZD->id,
            ],
        ]);

        $this->assertEquals("processing", $api->routeStatus($doc, "Y-B"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-C"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-D"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-B"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-C"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-D"));

        $api->receiveFromOffice($doc, "Z-B");
        $api->receiveFromOffice($doc, "Z-C");
        $api->receiveFromOffice($doc, "Z-D");

        $this->assertEquals("processing", $api->routeStatus($doc, "Y-B"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-C"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-D"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-B"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-C"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-D"));

        $api->dumpTree($doc);

    }

    public function testFinalize1() {
        self::createUserOffices();

        $officeXA = Office::withUserName("X-A");
        $officeXB = Office::withUserName("X-B");
        $officeXC = Office::withUserName("X-C");

        $officeYA = Office::withUserName("Y-A");
        $officeYB = Office::withUserName("Y-B");
        $officeYC = Office::withUserName("Y-C");

        $officeZA = Office::withUserName("Z-A");
        $officeZB = Office::withUserName("Z-B");
        $officeZC = Office::withUserName("Z-C");

        $api = new DoctracAPI($officeXA->user);
        $doc = $api->dispatchDocument([
            "title"=>str_random(),
            "officeIds"=>[$officeXB->id, $officeXC->id],
            "type"=>"serial",
        ]);
        $api->receiveFromOffice($doc, "X-B");
        $api->forwardToOffice($doc, "X-C");
        $api->receiveFromOffice($doc, "X-C");
        $api->finalizeByOffice($doc, "X-C");
        $api->dumpErrors();
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $api->forwardToOffice($doc, "X-A");
        $api->receiveFromOffice($doc, "X-A");
        $api->finalizeByOffice($doc, "X-A");
        $api->dumpErrors();
        $this->assertFalse($api->hasErrors());

        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-C"));

        $api->forwardToOffice($doc, "X-B");
        $api->dumpErrors();
        $this->assertTrue($api->hasErrors());

        dump($api->getTree($doc));
    }

    public function testRejection1() {
        self::createUserOffices();

        $officeXA = Office::withUserName("X-A");
        $officeXB = Office::withUserName("X-B");
        $officeXC = Office::withUserName("X-C");

        $officeYA = Office::withUserName("Y-A");
        $officeYB = Office::withUserName("Y-B");
        $officeYC = Office::withUserName("Y-C");

        $officeZA = Office::withUserName("Z-A");
        $officeZB = Office::withUserName("Z-B");
        $officeZC = Office::withUserName("Z-C");


        $api = new DoctracAPI($officeXA->user);
        $doc = $api->dispatchDocument([
            "title"=>str_random(),
            "officeIds"=>[$officeXB->id, $officeXC->id],
            "type"=>"serial",
        ]);
        $this->assertFalse($api->hasErrors());
        $routeXB = $api->followMainRoute($doc)[1];

        $route = $api->receiveFromOffice($doc, "X-B");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "X-B"));

        $route = $api->forwardToOffice($doc, "X-C");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "X-C"));

        $route = $api->receiveFromOffice($doc, "X-C");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("processing", $api->routeStatus($doc, "X-C"));

        $route = $api->forwardToOffice($doc, "X-B");
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "X-C"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "X-B"));

        $api->rejectByOffice($doc, "X-B");
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $route = $api->receiveFromOffice($doc, "X-B");
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-C"));
        $this->assertEquals("processing", $api->routeStatus($doc, "X-B"));

        $api->rejectByOffice($doc, "X-B");
        $doc = $api->getDocument($doc->trackingId);
        $this->assertEquals("disapproved", $doc->state);
        //$this->assertEquals("rejected", $api->routeStatus($doc, "X-B"));

        $api->clearErrors();
        $route = $api->receiveFromOffice($doc, "X-C");
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $route = $api->receiveFromOffice($doc, "X-B");
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $route = $api->receiveFromOffice($doc, "X-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));

        $route = $api->forwardToOffice($doc, "X-B");
        $this->assertTrue($api->hasErrors());

        dump($api->getTree($doc));
    }

    public function testRejection2() {
        self::createUserOffices();

        $officeXA = Office::withUserName("X-A");
        $officeXB = Office::withUserName("X-B");
        $officeXC = Office::withUserName("X-C");

        $officeYA = Office::withUserName("Y-A");
        $officeYB = Office::withUserName("Y-B");
        $officeYC = Office::withUserName("Y-C");

        $officeZA = Office::withUserName("Z-A");
        $officeZB = Office::withUserName("Z-B");
        $officeZC = Office::withUserName("Z-C");


        $api = new DoctracAPI($officeXA->user);
        $doc = $api->dispatchDocument([
            "title"=>str_random(),
            "officeIds"=>[$officeYA->id, $officeYB->id, $officeYA->id],
            "type"=>"serial",
        ]);
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("delivering", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-A"));

        $api->receiveFromOffice($doc, "Y-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-A"));

        $api->forwardToOffice($doc, "Z-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("delivering", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-A"));

        $api->receiveFromOffice($doc, "Z-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-A"));

        $api->forwardToOffice($doc, "X-B");
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $api->forwardToOffice($doc, "Z-B");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "Z-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-B"));

        $api->receiveFromOffice($doc, "Z-B");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "Z-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-B"));

        $api->rejectByOffice($doc, "Z-B");

        $this->assertFalse($api->hasErrors());
        $this->assertEquals("*", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "Z-B"));

        $api->receiveFromOffice($doc, "Z-C");
        $this->assertTrue($api->hasErrors());
        $api->clearErrors();

        $api->receiveFromOffice($doc, "Z-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-A"));
        $this->assertEquals("*", $api->routeStatus($doc, "X-A"));

        // rejected documents should
        // not be detoured away back to the origin
        $api->forwardToOffice($doc, "Z-B");
        $api->dumpErrors();
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $api->forwardToOffice($doc, "X-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("delivering", $api->routeStatus($doc, "Z-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "X-A"));

        $api->receiveFromOffice($doc, "X-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "Z-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));

        $api->forwardToOffice($doc, "X-B");
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $api->rejectByOffice($doc, "X-A");
        $this->assertTrue($api->hasErrors());

        $api->dumpErrors();
        $api->dumpTree($doc);
    }

    public function testSerialRoutes() {
        self::createUserOffices();

        $rec   = Office::withUserName("X-A");
        $dests = Office::withUserNames(["X-B", "X-C"]);
        $officeIds = $dests->map(function($off) { return $off->id; });

        dump($officeIds->toArray());

        $api = new DoctracAPI($rec->user);
        $api->debug = true;

        $api->dispatchDocument(new ArrayObject([
            "title"=>str_random(),
        ]));
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $doc = $api->dispatchDocument(new ArrayObject([
            "title"=>str_random(),
            "officeIds"=>$officeIds,
            "type"=>"serial",
        ]));
        $this->assertFalse($api->hasErrors());
        $this->assertNotNull($doc);

        $origin = $api->origin($doc);
        $this->assertNotNull($origin);
        $this->assertEquals($origin->officeId, $rec->id);

        $this->assertNotNull($origin->nextRoute);
        $this->assertEquals($origin->nextRoute->officeId, $dests[0]->id);

        $this->assertEquals("delivering", $origin->status);
        $this->assertEquals("waiting", $origin->nextRoute->status);

        $waitingRoute = $api->findWaitingRoutes($doc, $origin->nextRoute->office)[0];
        $this->assertEquals($origin->nextRoute->id, optional($waitingRoute)->id);

        $api->clearErrors();
        $wroute = $api->receiveDocument($origin->nextRoute, $doc);
        $this->assertFalse($api->hasErrors());
        $this->assertEquals($waitingRoute->id, $wroute->id);
        $nextRoute = $origin->nextRoute;
        $origin = $api->origin($doc); // get recent changes

        $this->assertEquals("done", $origin->status);
        $this->assertEquals("processing", $origin->nextRoute->status);


        $api->clearErrors();
        $api->forwardDocument([
            "route" => $origin->nextRoute,
            "officeIds" => [],
        ]);
        $origin = $api->origin($doc);

        dump($api->followRoute($origin)->map(function($route) {
            return [$route->officeId, $route->office_name, $route->nextId, $route->senderId, $route->status];
        }));
        $this->assertEquals("done", $origin->status);
        $this->assertEquals("delivering", $origin->nextRoute->status);

        $api->forwardDocument([
            "route" => $origin,
            "officeIds" => [],
        ]);
        // cannot send since origin does not have the document
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $api->forwardDocument([
            "route" => $origin->nextRoute,
            "officeIds" => [],
        ]);
        // cannot send since origin does not have the document
        $this->assertTrue($api->hasErrors());
        $api->clearErrors();

        $wroute = $api->receiveDocument($origin->nextRoute->nextRoute, $doc);
        dump($api->getErrors());
        $this->assertEquals("done", $origin->status);
        $this->assertEquals("done", $origin->nextRoute->status);
        $this->assertEquals("processing", $origin->nextRoute->nextRoute->status);

        $api->clearErrors();
        $api->forwardDocument([
            "route" => $origin->nextRoute->nextRoute,
            "officeIds" => [],
        ]);
        // has error since no officeIds is empty and
        // there is no pre-given route
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $api->forwardDocument([
            "route" => $origin->nextRoute->nextRoute,
            "officeIds" => [$origin->id],
        ]);

        $rec_ = Office::withUserName("Y-A");

        $api->clearErrors();
        $api->forwardDocument([
            "route" => $origin->nextRoute->nextRoute,
            "officeIds" => [$rec_->id],
        ]);
        // cannot forward directly to non-local offices
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $api->forwardDocument([
            "route" => $origin->nextRoute->nextRoute,
            "officeIds" => [$origin->officeId],
        ]);
        $api->dumpErrors();
        $this->assertFalse($api->hasErrors());
        $origin = $api->origin($doc);

        $route = $origin->nextRoute->nextRoute;
        $this->assertNotNull($route->nextRoute);
        $this->assertEquals("delivering", $route->status);
        $this->assertEquals("waiting", $route->nextRoute->status);
        $this->assertEquals($origin->officeId, $route->nextRoute->officeId);


        $api->clearErrors();
        $api->receiveDocument($route->nextRoute);
        $api->dumpErrors();
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $route->status);
        $this->assertEquals("processing", $route->nextRoute->status);


        $route = $route->nextRoute;
        $api->clearErrors();
        $api->forwardDocument([
            "route"     => $route,
            "officeIds" => [$rec_->id],
        ]);
        $route = $api->getRoute($route->id);
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("delivering", $route->status);
        $this->assertEquals("waiting", $route->nextRoute->status);
        $this->assertEquals($rec_->id, $route->nextRoute->officeId);

        $api->receiveDocument($route->nextRoute);
        $route = $api->getRoute($route->id);
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $route->status);
        $this->assertEquals("processing", $route->nextRoute->status);


        $api->clearErrors();
        $route = $api->getRoute($route->nextRoute->id);
        $dests = Office::withUserNames(["Y-B", "Y-C", "Y-D"]);
        $api->forwardDocument([
            "route"     => $route,
            "officeIds" => $dests->map(function($off) { return $off->id; }),
            "type"      => "parallel",
        ]);
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("delivering", $route->status);
        foreach ($route->allNextRoutes() as $nextRoute) {
            $this->assertEquals("waiting", $nextRoute->status);
        }

        $nextRoute = $route->allNextRoutes()[1];
        $api->receiveDocument($nextRoute);
        $this->assertFalse($api->hasErrors());
        $route = $api->getRoute($route->id);
        foreach ($route->allNextRoutes() as $nr) {
            if ($nr->id == $nextRoute->id)
                $this->assertEquals("processing", $nr->status);
            else
                $this->assertEquals("waiting", $nr->status);
        }
        $this->assertEquals("delivering", $route->status);

        $api->forwardDocument([
            "route"     => $nextRoute,
            "officeIds" => [$nextRoute->getRecordsOffice()->id],
            "type"      => "serial",
        ]);
        $route = $api->getRoute($route->id);
        $nextRoute = $route->allNextRoutes()[1];
        $api->dumpErrors();
        $this->assertFalse($api->hasErrors());
        foreach ($route->allNextRoutes() as $nr) {
            if ($nr->id == $nextRoute->id)
                $this->assertEquals("delivering", $nr->status);
            else
                $this->assertEquals("waiting", $nr->status);
        }
        $this->assertEquals("delivering", $route->status);


        foreach ($route->allNextRoutes() as $nr) {
            if ($nr->id == $nextRoute->id)
                continue;
            $api->receiveDocument($nr);
        }
        $route = $api->getRoute($route->id);
        $this->assertEquals("done", $route->status);
        foreach ($route->allNextRoutes() as $nr) {
            if ($nr->id == $nextRoute->id)
                $this->assertEquals("delivering", $nr->status);
            else
                $this->assertEquals("processing", $nr->status);
        }

        $nextRoutes = $route->allNextRoutes();
        $api->forwardDocument([
            "route"     => $nextRoutes->last(),
            "officeIds" => $nextRoutes->map(function($r) { return $r->officeId; }),
            "type"      => "parallel",
        ]);
        $api->dumpErrors();
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $api->forwardDocument([
            "route"     => $nextRoutes->last(),
            "officeIds" => [$nextRoutes->last()->officeId],
            "type"      => "serial",
        ]);
        $api->dumpErrors();
        $this->assertTrue($api->hasErrors());
        $this->assertEquals("processing", $nextRoutes->last()->status);


        $nextRoute = $nextRoutes->last();
        $destRoutes = $nextRoutes->filter(function($r) use ($nextRoute) {
            return $r->id != $nextRoute->id;
        });


        $api->clearErrors();
        $api->forwardDocument([
            "route"     => $nextRoutes->last(),
            "officeIds" => $destRoutes->map(function($r) { return $r->officeId; }),
            "type"      => "parallel",
        ]);
        $api->dumpErrors();
        $this->assertFalse($api->hasErrors());

        dump($api->getTree($doc));
    }

    public function testGetters() {
        $api = new DoctracAPI(\App\User::first());

        $offices = self::createUserOffices();
        $office1 = $offices[0];
        dump($office1->toArray());
        $office2 = $api->getOffice($office1);
        $office3 = $api->getOffice($office1->id);
        $office4 = $api->getOffice($office1->user);
        $office5 = $api->getOffice($office1->user->username);
        $this->assertEquals($office1->id, $office2->id);
        $this->assertEquals($office1->id, $office3->id);
        $this->assertEquals($office1->id, $office4->id);
        $this->assertEquals($office1->id, $office5->id);

        $doc1 = new \App\Document();
        $doc1->title = str_random();
        $doc1->userId = -1;
        $doc1->trackingId = str_random();
        $doc1->save();

        $doc2 = $api->getDocument($doc1);
        $doc3 = $api->getDocument($doc1->id);
        $doc4 = $api->getDocument($doc1->trackingId);
        $this->assertEquals($doc1->id, $doc2->id);
        $this->assertEquals($doc1->id, optional($doc3)->id);
        $this->assertEquals($doc1->id, optional($doc4)->id);

        $route1 = new \App\DocumentRoute();
        $route1->trackingId = str_random();
        $route1->officeId = $office1->id;
        $route1->save();

        $route2 = $api->getRoute($route1);
        $route3 = $api->getRoute($route1->id);
        $this->assertEquals($route1->id, $route2->id);
        $this->assertEquals($route1->id, $route3->id);
    }

    public function testAbb() {
        $officeA = new \App\Office();

        $this->assertTrue(true);
        fwrite(STDERR, print_r(self::createCampuses()->toArray(), TRUE));
    }

    public static function createUserOffices() {
        $campuses = self::createCampuses();
        $offices = collect();

        $id = 90000;
        foreach ($campuses as $campus) {
            $gateway = true;
            foreach (["A", "B", "C", "D", "E"] as $name) {
                $office = \App\Office::firstOrNew([
                    "id"=>$id++,
                    "name"=>$name,
                    "campusId"=>$campus->id,
                    "gateway"=>$gateway,
                ]);
                $office->save();
                $offices->push($office);
                $gateway = false;
                $user = \App\User::firstOrNew([
                    "firstname"=>$name,
                    "lastname"=>$campus->name,
                ]);

                $user->officeId = $office->id;
                $user->privilegeId = -1;
                $user->positionId = -1;
                $user->username = "{$campus->name}-{$name}";
                $user->password = bcrypt("x");
                $user->save();

            }
        }
        return $offices;
    }

    public static function createCampuses() {
        return collect([
            \App\Campus::firstOrCreate(["id"=>80001, "code"=> "cx", "name"=>"X"]),
            \App\Campus::firstOrCreate(["id"=>80002, "code"=> "cy", "name"=>"Y"]),
            \App\Campus::firstOrCreate(["id"=>80003, "code"=> "cz", "name"=>"Z"]),
        ]);
    }
}

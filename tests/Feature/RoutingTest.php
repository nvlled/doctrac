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

        $waitingRoute = $api->findWaitingRoute($doc, $origin->nextRoute->office);
        $this->assertEquals($origin->nextRoute->id, optional($waitingRoute)->id);

        $api->clearErrors();
        $wroute = $api->receiveDocument($origin->nextRoute->office, $doc);
        $this->assertEquals($waitingRoute->id, $wroute->id);
        //$api->setReceiver($origin->nextRoute, $origin->nextRoute->office->user);
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


        $route = $api->getRoute($route->nextRoute->id);
        $dests = Office::withUserNames(["Y-B", "Y-C", "Y-D"]);
        $api->forwardDocument([
            "route"     => $route,
            "officeIds" => $dests->map(function($off) { return $off->id; }),
            "type"      => "parallel",
        ]);

        dump($api->getTree($doc));
    }

    public function testGetters() {
        $api = new DoctracAPI();

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

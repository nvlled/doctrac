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

    public function testParallel() {
        self::createUserOffices();

        // make X-A the main
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
        dump("testing {$doc->trackingId}");

        $this->assertEquals("delivering", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-A"));
        $this->assertFalse($api->hasErrors());

        $api->setUser("Y-A")->receiveFromOffice($doc, "Y-A");
        $api->dumpErrors();
        $this->assertFalse($api->hasErrors());
        $api->setUser("Z-A")->receiveFromOffice($doc, "Z-A");
        $this->assertFalse($api->hasErrors());

        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-A"));

        $doc = $api->setUser("X-A")->dispatchDocument([
            "title"=>str_random(),
            "officeIds"=>[
                $officeYA->id,
                $officeZA->id,
            ],
            "type"=>"parallel",
        ]);
        $this->assertFalse($api->hasErrors());
        dump("testing {$doc->trackingId}");

        $api->setUser("Y-A")->receiveFromOffice($doc, "Y-A");
        $api->dumpErrors();
        $this->assertFalse($api->hasErrors());

        //$api->forwardToOffices($doc, ["Y-B", "Y-C", "Y-D"]); TODO
        $api->setUser("Y-A")->forwardDocument([
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
        $this->assertEquals("delivering", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-A"));

        $api->setUser("Z-A")->receiveFromOffice($doc, "Z-A");
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-A"));

        $api->setUser("Y-B")->receiveFromOffice($doc, "Y-B");
        $this->assertEquals("done", $api->routeStatus($doc, "Y-B"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-C"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-D"));

        $api->setUser("Y-C")->receiveFromOffice($doc, "Y-C");
        $api->setUser("Y-D")->receiveFromOffice($doc, "Y-D");

        $this->assertEquals("done", $api->routeStatus($doc, "Y-C"));
        $this->assertEquals("done", $api->routeStatus($doc, "Y-D"));

        $api->setUser("Z-A")->forwardDocument([
            "document"=>$doc,
            "office"  =>"Z-A",
            "type"    =>"parallel",
            "officeIds"=>[
                $officeZB->id,
                $officeZC->id,
                $officeZD->id,
            ],
        ]);

        $this->assertEquals("done", $api->routeStatus($doc, "Y-B"));
        $this->assertEquals("done", $api->routeStatus($doc, "Y-C"));
        $this->assertEquals("done", $api->routeStatus($doc, "Y-D"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-B"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-C"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-D"));

        $api->setUser("Z-B")->receiveFromOffice($doc, "Z-B");
        $api->setUser("Z-C")->receiveFromOffice($doc, "Z-C");
        $api->setUser("Z-D")->receiveFromOffice($doc, "Z-D");

        $this->assertEquals("done", $api->routeStatus($doc, "Y-B"));
        $this->assertEquals("done", $api->routeStatus($doc, "Y-C"));
        $this->assertEquals("done", $api->routeStatus($doc, "Y-D"));
        $this->assertEquals("done", $api->routeStatus($doc, "Z-B"));
        $this->assertEquals("done", $api->routeStatus($doc, "Z-C"));
        $this->assertEquals("done", $api->routeStatus($doc, "Z-D"));

        $api->dumpTree($doc);

    }

    public function testLevels() {
        $officeXA = Office::withUserName("X-A");
        $officeXB = Office::withUserName("X-B");
        $officeYA = Office::withUserName("Y-A");
        $this->assertEquals(3, $officeXA->level);
        $this->assertEquals(2, $officeYA->level);
        $this->assertEquals(1, $officeXB->level);
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
        dump("testing {$doc->trackingId}");
        $api->setUser("X-B")->receiveFromOffice($doc, "X-B");
        $this->assertFalse($api->hasErrors());
        $api->setUser("X-B")->forwardToOffice($doc, "X-C");
        $this->assertFalse($api->hasErrors());
        $api->setUser("X-C")->receiveFromOffice($doc, "X-C");
        $this->assertFalse($api->hasErrors());

        $api->setUser("X-C")->finalizeByOffice($doc, "X-C");
        $this->assertTrue($api->hasErrors());
        $api->clearErrors();

        $api->setUser("X-C")->forwardToOffice($doc, "X-A");
        $api->setUser("X-A")->receiveFromOffice($doc, "X-A");
        $api->setUser("X-A")->finalizeByOffice($doc, "X-A");
        $this->assertFalse($api->hasErrors());

        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-C"));

        $api->setUser("X-A")->forwardToOffice($doc, "X-B");
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
        dump("testing {$doc->trackingId}");
        $this->assertFalse($api->hasErrors());
        $routeXB = $api->followMainRoute($doc)[1];

        $route = $api->setUser("X-B")->receiveFromOffice($doc, "X-B");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "X-B"));

        $route = $api->setUser("X-B")->forwardToOffice($doc, "X-C");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "X-C"));

        $route = $api->setUser("X-C")->receiveFromOffice($doc, "X-C");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("processing", $api->routeStatus($doc, "X-C"));

        $route = $api->setUser("X-C")->forwardToOffice($doc, "X-B");
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "X-C"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "X-B"));

        $api->setUser("X-B")->rejectByOffice($doc, "X-B");
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $route = $api->setUser("X-B")->receiveFromOffice($doc, "X-B");
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-C"));
        $this->assertEquals("processing", $api->routeStatus($doc, "X-B"));

        $api->setUser("X-B")->rejectByOffice($doc, "X-B");
        $doc = $api->getDocument($doc->trackingId);
        $this->assertEquals("disapproved", $doc->state);

        $api->clearErrors();
        $route = $api->setUser("X-C")->receiveFromOffice($doc, "X-C");
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $route = $api->setUser("X-B")->receiveFromOffice($doc, "X-B");
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $route = $api->setUser("X-A")->receiveFromOffice($doc, "X-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));

        $route = $api->setUser("X-A")->forwardToOffice($doc, "X-B");
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
        dump("testing {$doc->trackingId}");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("delivering", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-A"));

        $api->setUser("Y-A")->receiveFromOffice($doc, "Y-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-A"));

        $api->setUser("Y-A")->forwardToOffice($doc, "Z-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("delivering", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-A"));

        $api->setUser("Z-A")->receiveFromOffice($doc, "Z-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-A"));

        $api->setUser("Z-A")->forwardToOffice($doc, "X-B");
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $api->setUser("Z-A")->forwardToOffice($doc, "Z-B");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "Z-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-B"));

        $api->setUser("Z-B")->receiveFromOffice($doc, "Z-B");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "Z-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-B"));

        $api->setUser("Z-B")->rejectByOffice($doc, "Z-B");
        $api->dumpErrors();

        $this->assertFalse($api->hasErrors());
        $this->assertEquals("*", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Z-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "Z-B"));

        $api->setUser("Z-C")->receiveFromOffice($doc, "Z-C");
        $this->assertTrue($api->hasErrors());
        $api->clearErrors();

        $api->setUser("Z-A")->receiveFromOffice($doc, "Z-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("processing", $api->routeStatus($doc, "Z-A"));
        $this->assertEquals("*", $api->routeStatus($doc, "X-A"));

        // rejected documents should
        // not be detoured away back to the origin
        $api->setUser("Z-A")->forwardToOffice($doc, "Z-B");
        $api->dumpErrors();
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $api->setUser("Z-A")->forwardToOffice($doc, "X-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("delivering", $api->routeStatus($doc, "Z-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "X-A"));

        $api->setUser("X-A")->receiveFromOffice($doc, "X-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "Z-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));

        $api->setUser("X-A")->forwardToOffice($doc, "X-B");
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $api->setUser("X-A")->rejectByOffice($doc, "X-A");
        $this->assertTrue($api->hasErrors());

        $api->dumpErrors();
        $api->dumpTree($doc);
    }

    public function testSerial1() {
        self::createUserOffices();

        $officeXA = Office::withUserName("X-A");
        $officeXB = Office::withUserName("X-B");
        $officeXC = Office::withUserName("X-C");
        $officeYA = Office::withUserName("Y-A");
        $officeYB = Office::withUserName("Y-B");
        $officeYC = Office::withUserName("Y-C");
        $officeYD = Office::withUserName("Y-D");

        $api = new DoctracAPI($officeXA->user);
        $doc = $api->dispatchDocument([
            "title"=>str_random(),
            "officeIds"=>[
                $officeXB->id,
                $officeXC->id,
            ],
            "type"=>"serial",
        ]);
        dump("testing {$doc->trackingId}");

        $this->assertFalse($api->hasErrors());
        $this->assertEquals("delivering", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("*", $api->routeStatus($doc, "X-C"));

        $api->setUser("X-C")->receiveFromOffice($doc, "X-C");
        $this->assertTrue($api->hasErrors());
        $api->clearErrors();

        $api->setUser("X-B")->receiveFromOffice($doc, "X-B");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("*", $api->routeStatus($doc, "X-C"));

        $api->setUser("X-B")->forwardToOffice($doc, "X-D");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "X-D"));
        $this->assertEquals("", $api->routeStatus($doc, "X-C"));

        $api->setUser("X-D")->receiveFromOffice($doc, "X-D");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("processing", $api->routeStatus($doc, "X-D"));
        $this->assertEquals("", $api->routeStatus($doc, "X-C"));

        $api->setUser("X-D")->forwardToOffice($doc, "X-C");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "X-D"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "X-C"));

        $api->setUser("X-C")->receiveFromOffice($doc, "X-C");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-D"));
        $this->assertEquals("processing", $api->routeStatus($doc, "X-C"));

        $api->setUser("X-C")->forwardToOffice($doc, "Y-C");
        $this->assertTrue($api->hasErrors());
        $api->clearErrors();
        $api->setUser("X-C")->forwardToOffice($doc, "Y-A");
        $this->assertTrue($api->hasErrors());
        $api->clearErrors();

        $api->setUser("X-C")->forwardToOffice($doc, "X-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("waiting", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-D"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "X-C"));

        $api->setUser("X-A")->receiveFromOffice($doc, "X-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("processing", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-D"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-C"));

        $api->setUser("X-A")->forwardToOffice($doc, "Y-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("delivering", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-D"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-C"));

        $api->setUser("X-A")->forwardToOffice($doc, "Z-C");
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("", $api->routeStatus($doc, "Z-A"));
        $this->assertTrue($api->hasErrors());
        $api->clearErrors();
        $api->setUser("Z-A")->receiveFromOffice($doc, "Z-A");
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("", $api->routeStatus($doc, "Z-A"));
        $this->assertTrue($api->hasErrors());
        $api->clearErrors();

        $api->setUser("Y-A")->receiveFromOffice($doc, "Y-A");
        $this->assertFalse($api->hasErrors());
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-D"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-C"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-A"));

        $api->setUser("Y-A")->forwardToOffice($doc, "Y-B");
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-D"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-C"));
        $this->assertEquals("delivering", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("waiting", $api->routeStatus($doc, "Y-B"));

        $api->setUser("Y-B")->receiveFromOffice($doc, "Y-B");
        $this->assertEquals("done", $api->routeStatus($doc, "X-A"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-B"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-D"));
        $this->assertEquals("done", $api->routeStatus($doc, "X-C"));
        $this->assertEquals("done", $api->routeStatus($doc, "Y-A"));
        $this->assertEquals("processing", $api->routeStatus($doc, "Y-B"));
    }

    public static function createUserOffices() {
        if (Office::withUsername("X-A"))
            return;
        $campuses = self::createCampuses();
        $offices = collect();

        $id = 90000;
        $main = true;
        foreach ($campuses as $campus) {
            $gateway = true;
            foreach (["A", "B", "C", "D", "E"] as $name) {
                $office = \App\Office::firstOrNew([
                    "id"=>$id++,
                    "name"=>$name,
                    "campusId"=>$campus->id,
                    "gateway"=>$gateway,
                    "main"=>$main,
                ]);
                $office->save();
                $offices->push($office);
                $gateway = false;
                $main = false;
                $user = \App\User::firstOrNew([
                    "firstname"=>$name,
                    "lastname"=>$campus->name,
                ]);

                $user->officeId = $office->id;
                $user->positionId = -1;
                $user->username = "{$campus->name}-{$name}";
                $user->admin = $user->username == "X-A";
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

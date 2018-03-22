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

        $api = new DoctracAPI();
        $api->dispatchDocument($rec->user, new ArrayObject([
            "title"=>str_random(),
        ]));
        $this->assertTrue($api->hasErrors());

        $api->clearErrors();
        $doc = $api->dispatchDocument($rec->user, new ArrayObject([
            "title"=>str_random(),
            "officeIds"=>$officeIds,
            "type"=>"serial",
        ]));
        dump($api->getErrors());
        $this->assertFalse($api->hasErrors());
        $this->assertNotNull($doc);
        dump($doc->toArray());

        $origin = $api->origin($doc);
        $this->assertNotNull($origin);
        $this->assertEquals($origin->officeId, $rec->id);
        dump($origin->toArray());
        dump($api->followRoute($origin)->map(function($route) {
            return [$route->officeId, $route->office_name, $route->id];
        }));

        $this->assertNotNull($origin->nextRoute);
        $this->assertEquals($origin->nextRoute->officeId, $dests[0]->id);

        $this->assertEquals("delivering", $origin->status);
        $this->assertEquals("waiting", $origin->nextRoute->status);

        //$api->receiveDocument($origin->nextRoute->office, $doc);
        $api->setReceiver($origin->nextRoute, $origin->nextRoute->office->user);
        $nextRoute = $origin->nextRoute;

        dump("X", $nextRoute->office->user->toArray(), "X");
        dump("X", $nextRoute->office->toArray(), "X");
        dump($api->getErrors());
        dump($origin->toArray());
        dump($nextRoute->toArray());
        dump($api->followRoute($origin)->map(function($route) {
            return [$route->officeId, $route->office_name, $route->id, $route->status, $route->receiverId];
        }));
        //eval (\Psy\sh());
        $this->assertEquals("done", $origin->status);
        $this->assertEquals("processing", $origin->nextRoute->status);
    }

    public function testAbb() {
        $officeA = new \App\Office();

        $this->assertTrue(true);
        dump ("blah");
        fwrite(STDERR, print_r(self::createCampuses()->toArray(), TRUE));
    }

    public static function createUserOffices() {
        $campuses = self::createCampuses();
        $offices = collect();

        $id = 90000;
        foreach ($campuses as $campus) {
            $gateway = true;
            foreach (["A", "B", "C"] as $name) {
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

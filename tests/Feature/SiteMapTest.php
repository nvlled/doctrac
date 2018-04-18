<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Sunra\PhpSimple\HtmlDomParser;

class SiteMapTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testPages()
    {
        $api = api();
        $users = [
            null,
            $api->getUser("main-records"),
            $api->getUser("urd-records"),
            $api->getUser("urd-mis"),
            $api->getUser("X-A"),
        ];
        foreach ($users as $user)
            $this->crawlSite($user);
    }

    function crawlSite($user) {
        $hostname = request()->getHost();
        $visited = collect();
        $toVisit = collect("/");

        $username = $user ? $user->username : "*anonymous";
        echo "crawling site as user : {$username}\n";
        while ($toVisit->count() > 0) {
            $link = $toVisit->pop();
            if ($visited->get($link))
                continue;

            $visited[$link] = true;
            if ($user)
                $this->actingAs($user);
            $response = $this->get($link);

            echo "* crawling $link\n";
            if ($this->isRedirect($response)) {
                \Log::debug("redirecting");
                $toVisit->push($response->headers->get("location"));
                continue;
            }
            $response->assertSuccessful();

            $html = HtmlDomParser::str_get_html($response->getContent());
            foreach ($html->find("a") as $a) {
                if (!trim($a->href) ||
                    $this->isHash($a->href) ||
                    !$this->isLocal($a->href)) {
                    continue;
                }
                //echo "next link: '{$a->href}'\n";

                $url = parse_url($a->href);
                $nextLink = ($url["path"] ?? $link);
                if (@$url["query"]) {
                    $nextLink = replaceQueryString($nextLink, $url["query"]);
                }

                if ( ! $visited->get($nextLink))
                    $toVisit->push($nextLink);
            }
        }
    }

    function isRedirect($response) {
        $isRedirectCode = floor($response->status()/100) == 3;
        return $isRedirectCode && !!$response->headers->get("location");
    }

    function isHash($link) {
        if (!$link)
            return;
        return trim($link)[0] == "#";
    }

    function isLocal($link) {
        $data = parse_url($link);
        $hostname = $data["host"] ?? parse_url(env("APP_URL"))["host"];
        $port = $data["port"] ?? 80;
        $req = request();

        $result = $req->getHost() == $hostname &&
            $req->getPort() == $port;
        $val = $result ? "yes" : "no";
        //echo ": {$req->getHost()} {$req->getPort()}\n";
        //echo ": isLocal: $hostname $port = $val\n";

        return $result;

    }
}

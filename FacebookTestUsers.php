<?php
/**
 * Simple class for managing Facebook test users
 *
 * PHP version 5
 *
 * Copyright (c) 2011 Martin Rio axolx@fastmail.fm, except where otherwise noted.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * The Software shall be used for Good, not Evil
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @category Facebook
 * @package  FacebookTestUser
 * @author   Martin Rio <axolx@fastmail.fm>
 * @license  See above
 * @version  alpha
 *
 */

class FacebookTestUsers {

    public $fb;
    public $appId;

    protected $tu;
    protected $_cache;

    /**
     * @param $fb instance of Facebook SDK
     * @param $appId
     */
    public function __construct($fb, $appId)
    {
        $this->fb = $fb;
        $this->appId = $appId;
        $this->tu = '/' . $this->appId . '/accounts/test-users';
    }

    /**
     * Fooo
     *
     * @param int $number The number of users to Created
     *
     * @return FbTestUsers
     */
    public function add($number = 1)
    {
       for($i = 0; $i < $number; ++$i) {
           $u = $this->fb->api( $this->tu, 'POST');
           echo "Created test user: " . $u['id'] . PHP_EOL;
       }
       $this->_cache['all'] = null;
       return $u;
    }

    public function all($print = false)
    {
        if ($this->_cache['all']) {
            $r = $this->_cache['all'];
        } else {
            $r = $this->fb->api( $this->tu );
            $this->_cache['all'] = $r;
        }
        if ($print) {
            foreach($r['data'] as $u) {
                $this->echoUser($u);
            }
            return;
        } else {
            return $r['data'];
        }
    }

    public function clear()
    {
        $r = $this->fb->api( $this->tu );
        foreach($r['data'] as $u) {
            $url = sprintf('/' . $u['id']);
            $this->fb->api($url, 'DELETE');
            echo "Deleted test user: " . $u['id'] . PHP_EOL;
        }
        $this->_cache['all'] = null;
    }

    /**
     * @param array $u1
     *      A FB Graph API test user
     * @param array $u2
     *      A FB Graph API test user
     */
     public function befriend($u1, $u2)
     {
        if($u1['id'] == $u2['id']) return false;
        try {
            $this->fb->api(
                '/me/friends/' . $u2['id'], 'POST',
                array('access_token' => $u1['access_token'])
            );
            $this->fb->api(
                '/me/friends/' . $u1['id'], 'POST', array('access_token' =>
                $u2['access_token'])
            );
            echo "User " . $u1['id'] . " befriended " . $u2['id'] . PHP_EOL;
            return true;
        } catch(FacebookApiException $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * Make random friend connections between the app's users
     *
     * @param $chance
     * @param $maxFriends
     */
     public function befriendAll($chance = 0.5, $maxFriends = false)
     {
        $users = $this->all();
        $maxFriends = $maxFriends ? $maxFriends : count($users) - 1;
        foreach ($this->all() as $u) {
            if(rand(1,100)/100 < 1 - $chance) continue;
            $numFriends = rand(1, $maxFriends);
            for ($i = 1; $i <= $numFriends; ++$i)  {
                $friend = $users[rand(0, count($users) - 1)];
                $this->befriend($u, $friend);
            }
        }
    }

    /**
     * @param array $u
     *      A FB Graph API test users
     */
    public function echoUser($u)
    {
        printf('ID: %s' . PHP_EOL, $u['id']);
        echo "- Friends:" . PHP_EOL;
        $r = $this->fb->api( '/me/friends', array('access_token' => $u['access_token']));
        foreach ($r['data'] as $f) {
            printf("\t - ID: %s" . PHP_EOL, $f['id']);
        }
    }
}

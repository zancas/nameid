<?php
/*
    NameID, a namecoin based OpenID identity provider.
    Copyright (C) 2014 by Daniel Kraft <d@domob.eu>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/* Ajax query provider to submit a chat message.  */

require_once ("../lib/config.inc.php");

require_once ("../lib/chat.inc.php");
require_once ("../lib/database.inc.php");
require_once ("../lib/json.inc.php");
require_once ("../lib/request.inc.php");
require_once ("../lib/session.inc.php");

// Construct the basic worker classes.
$session = new Session ($sessionName);
$db = new Database ($dbHost, $dbUser, $dbPassword, $dbName);
$c = new Chat ($db);
$req = new RequestHandler ();
$json = new JsonSender (true);

// Check that a user is logged in.
$loggedIn = $session->getUser ();
if ($loggedIn === NULL)
  throw new RuntimeException ("No user is logged in.");

// Insert submitted message.
if (!$req->check ("message"))
  throw new RuntimeException ("No chat message given.");
$msg = $req->getString ("message");
$c->submitMessage ($loggedIn, $msg);

// Query for messages and send them.
$obj = $json->sendObject ();
$arr = $obj->sendArray ("messages");
if ($req->check ("since"))
  $c->queryNew ($arr, $req->getInteger ("since"));
else
  $c->queryNew ($arr);
$arr->close ();
$obj->close ();

// Close everything.
$json->close ();
$req->close ();
$c->close ();
$db->close ();
$session->close ();

?>

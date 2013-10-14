<html>
<p>Hello <?= $this->firstName ?> <?= $this->lastName ?></p>

<p>
You have one club request to join <?= $this->clubName ?>.<br />
In order to join this club, please connect to http://www.investiclub.net/ with your login and password as follow:</p>

<p>
<strong>Login:    <?= $this->email ?></strong>
<strong>Password: <?= $this->password ?></strong>
</p></html>
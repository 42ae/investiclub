<html>
<p>Hello,<br />

To confirm that <?= $this->firstName ?> <?= $this->lastName ?> is a member of your club <?= $this->clubName ?>, please click on the link below.<br />
<a href="http://www.investiclub.net<?= $this->url(array('controller' => 'clubs', 'action' => 'join', 'accept-member' => $this->encryptedMemberId), 'default', true) ?>">I accept <?= $this->firstName ?> <?= $this->lastName ?> as member of my club.</a>
</p></html>
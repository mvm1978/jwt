<?php

$temporaryPassword = $info['password'];
$user = trim($info['first_name'] . ' ' . $info['last_name']);

$comma = $user ? ',' : NULL; ?>

<h3>Hello<?php echo $comma . ' ' . $user; ?></h3>
<p>You receive this email because you have requested password recovery at MVM</p>
<p>Your username is <strong><?php echo $info['username']; ?></strong></p>
<p>Your temporary password is <strong><?php echo $temporaryPassword; ?></strong></p>
<p>Use the temporary password to sign in and change your password</p>
<p>Note: the temporary password will expire in 5 minutes! If you fail to change the temporary password within 5 minutes you will have to request a new password again!</p>
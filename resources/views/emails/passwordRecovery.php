<?php

$ulr = $info['url'] . '?password-recovery-token=' . $info['token'];
$user = trim($info['first_name'] . ' ' . $info['last_name']);

$comma = $user ? ',' : NULL; ?>

<h3>Hello<?php echo $comma . ' ' . $user; ?></h3>
<p>You receive this email because you have requested password recovery at Virtual Library</p>
<p>Your username is <strong><?php echo $info['username']; ?></strong></p>
<p>Follow this link to reset you password <a href="<?php echo $ulr; ?>" target="_blank"><?php echo $ulr; ?></a></p>
<p>Note: the link will expire in 5 minutes! If you fail to change the password within 5 minutes you will have to start a new password recovery process again!</p>
<p>After resetting you password Sing In using your username and new password</p>
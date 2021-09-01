<div>
	<div> Hello <?php echo $student["First_Name"]." ".$student["Last_Name"] ?>,</div>
	<br>
	<p> You haven't completed the peer assessment for <b><?php echo $assessment["Name"]?></b>.<br>
		The deadline is <b><?php echo $assessment["Deadline"]?></b>.
		You can access the website at <?php echo $link; ?> to complete the assessment.
		<br>
		<br>
		Kind Regards,
		<br>
		<br>
		<?php echo $lecturer["First_Name"]." ".$lecturer["Last_Name"] ?>
	</p>
</div>
<div>
	<div> Hello <?php echo $student["First_Name"]." ".$student["Last_Name"] ?>,</div>

	<p> 
		You have received <?php 
		if($student["Individual_Mark"][0]=="A" || $student["Individual_Mark"][0]=="a") echo "an ";
		else echo "a ";?>
		<b>
		<?php
		echo $student["Individual_Mark"]; ?></b>.
		<br>
		Your group has received <?php 
		if(substr($student["Group_Mark"], 0,1)=="A" || substr($student["Group_Mark"], 0,1)=="a") echo "an ";
		else echo "a ";?><b><?php echo $student["Group_Mark"]; ?></b>. 
		<br>
		<?php if(!empty($student["Lecturer_Comment"])){ ?>
		<h4>Group Comment:</h4>
		<?php echo $student["Lecturer_Comment"]; ?>
		<?php }
		if(!empty($student["Comment"])){ ?>
		<h4>Invididual Comment:</h4>
		<?php echo $student["Comment"]; ?>
		<?php } 
		if(!empty($student["Filepath"])){
		?>
		<br>
		<br>
		<br>
		A group feedback file has been attached!
		<?php } 
		?>
		<br>
		<br>
		<br>
		Kind Regards,
		<br>
		<br>
		<?php echo $lecturer["First_Name"]." ".$lecturer["Last_Name"] ?>
	</p>
</div>


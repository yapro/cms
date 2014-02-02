<?php	

require_once( './my_twitter.php' );

$twitter =  new MyTwitter('TwitterUser', 'TwitterPassword');

$status = $twitter->userTimeLine();

$total = count($status);

	
for ( $i=0; $i < $total ; $i++ )
		{ 
		
		echo "<p>". $status[$i]['text'] ."</p>";
		
		}
		
?>














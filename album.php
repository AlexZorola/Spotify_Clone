<?php include("includes/includedFiles.php"); 

if(isset($_GET['id'])) {
	$albumId = $_GET['id'];
}
else {
	header("Location: index.php");
}

$album = new Album($con, $albumId);
$artist = $album->getArtist();
$artistId = $artist->getId();
?>

<div class="entityInfo">

	<div class="leftSection">
		<img src="<?php echo $album->getArtworkPath(); ?>">
	</div>

	<div class="rightSection">
		<h2><?php echo $album->getTitle(); ?></h2>
		<p role="link" tabindex="0" onclick="openPage('artist.php?id=<?php echo $artist->getId(); ?>')">By <?php echo $artist->getName(); ?></p>
		<p><?php echo $album->getNumberOfSongs(); ?> songs</p>

	</div>

</div>

<div class="tracklistContainer">
	
	<ul class="tracklist">

		<?php
			$songIdArray = $album->getSongIds();

			$i = 1;
			foreach($songIdArray as $songId){

				$albumSong = new Song($con, $songId);
				$albumArtist = $albumSong->getArtist();

				echo "<li class='tracklistRow borderBottom'>

						<div class='trackCount'>
							<img class='play' src='assets/images/icons/play_white.png' onclick='setTrack(\"". $albumSong->getId() ."\", tempPlaylist, true)'>
							<span class='trackNumber'>$i</span>
						</div>

						<div class='trackInfo'>
							<span class='trackName'>" . $albumSong->getTitle() . "</span>
							<span class='artistName'>" . $albumArtist->getName() . "</span>
						</div>

						<div class='trackOptions'>
							<input type='hidden' class='songId' value='" . $albumSong->getId() . "'>
							<img class='optionsButton' src='assets/images/icons/menu_white.png' onclick='showOptionsMenu(this)'>
						</div>

						<div class='trackDuration'>
							<span class='duration'>" . $albumSong->getDuration() . "</span>
						</div>


					</li>";

				$i++;

			}
		?>

		<script>
			var tempSongIds = '<?php echo json_encode($songIdArray); ?>'; //getting the array of songIds
			tempPlaylist = JSON.parse(tempSongIds); //contains the songs of the page, used the same way as current playlist and shuffleplaylist
		</script>
		
	</ul>

</div>

<nav class="optionsMenu">
	<input type="hidden" class="songId">
	<?php echo Playlist::getPlaylistsDropdown($con, $userLoggedIn->getUsername()); ?>
	<div class="item">Download</div>
	<div class="item">Tour Dates</div>
</nav>
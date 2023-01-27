<?php

$songQuery = mysqli_query($con, "SELECT id FROM songs ORDER BY RAND() LIMIT 10");

$resultArray = array();

while($row = mysqli_fetch_array($songQuery)){ //The mysqli_fetch_array() function fetches a result row as an associative array, a numeric array, or both.
	array_push($resultArray, $row['id']);
}

$jsonArray = json_encode($resultArray); //JSON = Javascript Object Notation storing data so that any other language can understand it
										//json_encode built in php function for converting objects to json
?>

<script>
	$(document).ready(function() { //Waits until the document(page) is completely ready to start rendering javascript before it does things
		var newPlaylist = <?php echo $jsonArray; ?>; //array of songId's
		audioElement = new Audio();
		setTrack(newPlaylist[0], newPlaylist, false); //play is false so that the song does not automatically play
		updateVolumeProgressBar(audioElement.audio);

		$("#nowPlayingBarContainer").on("mousedown touchstart mousemove touchmove", function(e) {
			e.preventDefault();
		});

		$(".playbackBar .progressBar").mousedown(function() {
			mouseDown = true;
		});

		$(".playbackBar .progressBar").mousemove(function(e) {
			if(mouseDown == true) {
				//Set time of song, depending on position of mouse
				timeFromOffset(e, this);
			}
		});

		$(".playbackBar .progressBar").mouseup(function(e) {
			timeFromOffset(e, this);
		});

		$(".volumeBar .progressBar").mousedown(function() {
			mouseDown = true;
		});

		$(".volumeBar .progressBar").mousemove(function(e) {
			if(mouseDown == true) {

				var percentage = e.offsetX / $(this).width();
				if(percentage >= 0 && percentage <= 1) {
					audioElement.audio.volume = percentage;
				}
				
			}
		});

		$(".volumeBar .progressBar").mouseup(function(e) {
			var percentage = e.offsetX / $(this).width();
				if(percentage >= 0 && percentage <= 1) {
					audioElement.audio.volume = percentage;
				}
		});

		$(document).mouseup(function() {
			mouseDown = false;
		});


	});

	function timeFromOffset(mouse, progressBar) {
		//how far along the X it is, then get the percentage from the whole bar
		var percentage = mouse.offsetX / $(progressBar).width() * 100;
		//taking the percentage, and setting the seconds to 50% of the total duration
		var seconds = audioElement.audio.duration * (percentage / 100);
		audioElement.setTime(seconds);
	};

	function prevSong() {
		if(audioElement.audio.currentTime >= 3 || currentIndex == 0) { //if the time of the song is greater than or equal to 3 seconds
																		//into the song OR 0 then reset the timer of the song to 0
																		//after getting to a certain point of the song it just restarts the song
																		//If they are at the first song of the playlist it just restarts the song
			audioElement.setTime(0);
		}
		else {
			currentIndex = currentIndex - 1;
			setTrack(currentPlaylist[currentIndex], currentPlaylist, true);//set the track, by  passing it the Id of the previous element in the array
																			//pass it the playlist, and set play to true
		}
	};

	function nextSong() {
		if(repeat == true) {
			audioElement.setTime(0);
			playSong();
			return;
		}
	 
		if(currentIndex == currentPlaylist.length - 1) {//if the current index is the last element of the array
			currentIndex = 0; //then go back to the start
		}
		else {
			currentIndex++;//else move on to the next element in the array
		}
	 
		var trackToPlay = shuffle ? shufflePlaylist[currentIndex] : currentPlaylist[currentIndex];
		setTrack(trackToPlay, currentPlaylist, true);
	}

	function setRepeat() {
		repeat = !repeat; //repeat equals to whatever repeat is not returns wether or not
		var imageName = repeat ? "repeat-active.png" : "repeat.png"
		$(".controlButton.repeat img").attr("src","assets/images/icons/" + imageName);
	};

	function setMute() {
		audioElement.audio.muted = !audioElement.audio.muted
		var imageName = audioElement.audio.muted ? "volume-mute.png" : "volume_1.png"
		$(".controlButton.volume img").attr("src","assets/images/icons/" + imageName);
	};

	function setShuffle() {
		shuffle = !shuffle;
		var imageName = shuffle ? "shuffle-active_1.png" : "shuffle_1.png"
		$(".controlButton.shuffle img").attr("src","assets/images/icons/" + imageName);

		if(shuffle == true) {
			//randomize playlist
			shuffleArray(shufflePlaylist);//contains a duplicate of the current playlist before shuffling it
			//Sets the current Index to be the index of the song that is currently playing
			//This prevents the same song from playing twice
			currentIndex = shufflePlaylist.indexOf(audioElement.currentlyPlaying.id);
		}
		else {
			//shuffle has been deactivated
			//go back to regular playlist
			//Sets the current Index to be the index of the current song that is playing
			//from the unshuffled playlist so the no song is played twice
			currentIndex = currentPlaylist.indexOf(audioElement.currentlyPlaying.id);
		}
	};

	function shuffleArray(a) {
	    var j, x, i;
	    for (i = a.length; i; i--) {
	        j = Math.floor(Math.random() * i);
	        x = a[i - 1];
	        a[i - 1] = a[j];
	        a[j] = x;
	    }
	};

	function setTrack(trackId, newPlaylist, play) {
		//if we select a new song it creates a new playlist
		//We need to keep track of two playlist so that we can move back and forth between them
		//If the user swaps back and forth from shuffle and unshuffle
		if(newPlaylist != currentPlaylist) { //checks to see if they are different
			currentPlaylist = newPlaylist; //makes the current playlist be the newplaylist and its unrandominzed
			shufflePlaylist = currentPlaylist.slice();//returns a copy of the array and saves it to shuffleplaylist
			shuffleArray(shufflePlaylist);//shuffle the playlist
		}
	 
		if(shuffle == true) {
			currentIndex = shufflePlaylist.indexOf(trackId);//make the currentIndex equal to the index of the songId
															//within the shuffle playlist
		}
		else {
			currentIndex = currentPlaylist.indexOf(trackId); //the current index is set to be the index of the trackId that was passed in
															//using the unshuffled playlist
		}
		pauseSong();
	 
		$.post("includes/handlers/ajax/getSongJson.php", { songId: trackId }, function(data) { //First parameter is the url of the page you want to render
																								//Second paramater is any data you want to send, songId is the name you're gonna use when you send it though, in the ajax page it will access the songId variable. Then trackId is the value being passed.
			var track = JSON.parse(data);
			$(".trackName span").text(track.title);
	 
			$.post("includes/handlers/ajax/getArtistJson.php", { artistId: track.artist }, function(data) { //We can only have the artistId whenthe ajax call with the 																									songId returns
				var artist = JSON.parse(data);
				$(".trackInfo .artistName span").text(artist.name);
				$(".trackInfo .artistName span").attr("onclick", "openPage('artist.php?id=" + artist.id + "')");
				$(document).attr("title", track.title + " - " + artist.name);
	 
			});
	 
			$.post("includes/handlers/ajax/getAlbumJson.php", { albumId: track.album }, function(data) { //We can only have the albumId whenthe ajax call with the 																									songId returns
				var album = JSON.parse(data);
				$(".content .albumLink img").attr("src", album.artworkPath);
				$(".content .albumLink img").attr("onclick", "openPage('album.php?id=" + album.id + "')");
				$(".trackInfo .trackName span").attr("onclick", "openPage('album.php?id=" + album.id + "')");
			});
	 
			audioElement.setTrack(track);
	 
			if(play == true) {
				playSong();
			}

		});
	}

	function playSong() {

		if(audioElement.audio.currentTime == 0) {
			$.post("includes/handlers/ajax/updatePlays.php", { songId: audioElement.currentlyPlaying.id });
		}

		$(".controlButton.play").hide();
		$(".controlButton.pause").show();
		audioElement.play();
	};

	function pauseSong() {
		$(".controlButton.play").show();
		$(".controlButton.pause").hide();
		audioElement.pause();
	};
</script>


<div id="nowPlayingBarContainer">
		
	<div id="nowPlayingBar">

		<div id="nowPlayingLeft">
			<div class="content">
				<span class="albumLink">
					<img role="link" tabindex="0" src="" class="albumArtwork">
				</span>

				<div class="trackInfo">
					
					<span class="trackName">
						<span role="link" tabindex="0"></span>
					</span>

					<span class="artistName">
						<span role="link" tabindex="0"></span>
					</span>

				</div>
			</div>
		</div>

		<div id="nowPlayingCenter">

			<div class="content playerControls">

				<div class="buttons">

					<button class="controlButton shuffle" title="Shuffle button" onclick="setShuffle()">
						<img src="assets/images/icons/shuffle_1.png" alt="Shuffle">
					</button>
					
					<button class="controlButton previous" title="Previous button" onclick="prevSong()">
						<img src="assets/images/icons/previous.png" alt="Previous">
					</button>

					<button class="controlButton play" title="Play button" onclick="playSong()">
						<img src="assets/images/icons/play_1.png" alt="Play">
					</button>

					<button class="controlButton pause" title="Pause button" style="display: none;" onclick="pauseSong()">
						<img src="assets/images/icons/pause.png" alt="Pause">
					</button>

					<button class="controlButton next" title="Next button" onclick="nextSong()">
						<img src="assets/images/icons/next.png" alt="Next">
					</button>

					<button class="controlButton repeat" title="Repeat button" onclick="setRepeat()">
						<img src="assets/images/icons/repeat.png" alt="Repeat">
					</button>

				</div>

				<div class="playbackBar">

					<span class="progressTime current">0.00</span>

					<div class="progressBar">
						<div class="progressBarBg">
							<div class="progress"></div>
						</div>
					</div>

					<span class="progressTime remaining">0.00</span>

					
				</div>
				
			</div>
			
		</div>

		<div id="nowPlayingRight">
			<div class="volumeBar">

				<button class="controlButton volume" title="Volume button" onclick="setMute()">
					<img src="assets/images/icons/volume_1.png" alt="Volume">
				</button>

				<div class="progressBar">
					<div class="progressBarBg">
						<div class="progress"></div>
					</div>
				</div>
				
			</div>
		</div>
		
	</div>

</div>
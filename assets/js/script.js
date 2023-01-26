var currentPlaylist = [];
var shufflePlaylist = [];
var tempPlaylist = [];
var audioElement;
var mouseDown = false;
var currentIndex = 0;
var repeat = false;
var shuffle = false;
var userLoggedIn;
var timer;

$(document).click(function(click) {
	var target = $(click.target);
	if(!target.hasClass("item") && !target.hasClass("optionsButton")) { //If the thing we clicked on does not have the class "item" && "optionsMenu"
		hideOptionsMenu(); //Hide the optionsMenu
	}
});

$(window).scroll(function() {
	hideOptionsMenu();
});

$(document).on("change", "select.playlist", function() {
	var select = $(this);
	var playlistId = select.val();
	var songId = select.prev(".songId").val(); //Prev takes the immediate ancestor, basically goes up one to find what it needs to

	$.post("includes/handlers/ajax/addToPlaylist.php", { playlistId: playlistId, songId: songId })
	.done(function(error) {

		if(error != "") {
				alert(error);
				return;
		}

		hideOptionsMenu();
		select.val("");
	});
});

function updatePassword(oldPasswordClass, newPasswordClass1, newPasswordClass2) {
	var oldPassword = $("." + oldPasswordClass).val();
	var newPassword1 = $("." + newPasswordClass1).val();
	var newPassword2 = $("." + newPasswordClass2).val();

	$.post("includes/handlers/ajax/updatePassword.php", 
		{ oldPassword: oldPassword, newPassword1: newPassword1, newPassword2: newPassword2, username: userLoggedIn })
	.done(function(response) {
		$("." + oldPasswordClass).nextAll(".message").text(response);
	});
}

function updateEmail(emailClass) {
	var emailValue = $("." + emailClass).val();

	$.post("includes/handlers/ajax/updateEmail.php", { email: emailValue, username: userLoggedIn })
	.done(function(response) {
		$("." + emailClass).nextAll(".message").text(response);
	});
}

function logout() {
	$.post("includes/handlers/ajax/logout.php", function() {
		location.reload();
	});
}

function openPage(url) {

	if(timer != null) {
		clearTimeout(timer);
	}

	if(url.indexOf("?") == -1) {
		url = url + "?";
	}

	var encodedUrl = encodeURI(url + "&userLoggedIn=" + userLoggedIn);
	$("#mainContent").load(encodedUrl);

	$("body").scrollTop(0); //When we change page we automatically scroll to the top
	history.pushState(null, null, url); //puts the url into the address bar and history
}

function removeFromPlaylist(button, playlistId) {
	var songId = $(button).prevAll(".songId").val();

	$.post("includes/handlers/ajax/removeFromPlaylist.php", { playlistId: playlistId, songId: songId })
	.done(function(error) {

		if(error != "") {
			alert(error);
			return;
		}
		//do something when ajax returns
		openPage("playlist.php?id=" + playlistId);
	});
}

function createPlaylist() {
	var popup = prompt("Please enter the name of your playlist");

	if(popup != null) {

		$.post("includes/handlers/ajax/createPlaylist.php", { name: popup, username: userLoggedIn }).done(function(error) {

			if(error != "") {
				alert(error);
				return;
			}
			//do something when ajax returns
			openPage("yourMusic.php");
		});
	}
};

function deletePlaylist(playlistId) {
	var prompt = confirm("Are you sure you want to delete this playlist?");

	if(prompt == true) {
		$.post("includes/handlers/ajax/deletePlaylist.php", { playlistId: playlistId }).done(function(error) {

			if(error != "") {
				alert(error);
				return;
			}
			//do something when ajax returns
			openPage("yourMusic.php");
		});
	}
}

function hideOptionsMenu() {
	var menu = $(".optionsMenu");
	if(menu.css("display") != "none") { //if the display menu is showing
		menu.css("display", "none"); //then make the display none, so it doesn't show
	}
}

function showOptionsMenu(button) {
	var songId = $(button).prevAll(".songId").val(); //PrevAll will go up multiple to find what it needs to
	var menu = $(".optionsMenu");
	var menuWidth = menu.width();
	menu.find(".songId").val(songId);

	var scrollTop = $(window).scrollTop(); //Distance from top of window to top of document
	var elementOffset = $(button).offset().top; // Gets the position of the button from the top of the document

	var top = elementOffset - scrollTop;
	var left = $(button).position().left;

	menu.css({ "top": top + "px", "left": left-menuWidth + "px", "display": "inline" });
}

function formatTime(seconds) {
	var time = Math.round(seconds);//contains the rounded version of the seconds
	var minutes = Math.floor(time / 60); //number of minutes we have, but might come out to be a decimal number, 
										//so we floor it, which rounds down
	var seconds = time - (minutes * 60);//seconds left over

	var extraZero; //for when the time is 8:3 -> 8:30 or 5.4 -> 5:04

	if(seconds < 10) { //less than 10 seconds
		extraZero = "0";
	}
	else {
		extraZero = "";
	}

	//var extraZero = (seconds < 10) ? "0" : ""; //can also be written like this means the same as the if and else

	return minutes + ":" + extraZero + seconds;
};

function updateTimeProgressBar(audio) {
	$(".progressTime.current").text(formatTime(audio.currentTime));//JQuery for setting the current time of the song
	$(".progressTime.remaining").text(formatTime(audio.duration - audio.currentTime));

	var progress = audio.currentTime / audio.duration * 100; //Calculate percentage for progress bar styling
	$(".playbackBar .progress").css("width", progress + "%");//width attribute with the progess as the value
};

function updateVolumeProgressBar(audio) {

	var volume = audio.volume * 100;
	$(".volumeBar .progress").css("width", volume + "%");
}

function  playFirstSong() {
	setTrack(tempPlaylist[0], tempPlaylist, true);
};

function Audio() {

	this.currentlyPlaying;
	this.audio = document.createElement('audio'); //Built in HTML audio element
	this.audio.addEventListener("ended", function() { //Event listeners listen for events
		nextSong();
	});

	this.audio.addEventListener("canplay", function() { //the audio object has a canplay event, 
		//													when the even fires then the success function happens
														//Also canplay means if the song can be played.
		//'this' refers to the object that the event was called on
		var duration = formatTime(this.duration);//formats the time to be nice user friendly
		$(".progressTime.remaining").text(duration);//JQuery for updating the time remaining of 
													//the song found in the nowPlayingBar.php HTML section
	});

	this.audio.addEventListener("timeupdate", function() { //
		if(this.duration) { //if it has a duration, else it's false or null
			updateTimeProgressBar(this);
		}
	});

	this.audio.addEventListener("volumechange", function() {
		updateVolumeProgressBar(this);
	});

	this.setTrack = function(track) {
		this.currentlyPlaying = track;
		this.audio.src = track.path;
	}

	this.play = function(){
		this.audio.play();
	}

	this.pause = function(){
		this.audio.pause();
	}

	this.setTime = function(seconds){
		//setting the current time to be the number of second passed in
		this.audio.currentTime = seconds;
	}

}
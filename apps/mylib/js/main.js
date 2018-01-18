$('button').click(function(){
	var path = $(this).attr('path');
	if (path && typeof(path) == 'string' && path !== '') location=path+''; 
});
$(function() { 
$("body").css({padding:0,margin:0});
  var f = function() {
    $("#page_content").css({position:"relative"});
    var h1 = $("body").height();
    var h2 = $(window).height();
    var d = h2 - h1;
    var h = $("#page_content").height() + d;    
    var ruler = $("<div>").appendTo("#page_content");       
    h = Math.max(ruler.position().top,h);
    ruler.remove();    
    $("#page_content").height(h);
  };
  setInterval(f,10); $(window).resize(f); f(); 
});

function errorMsg(msg) { showMessage(msg, "error"); }
function successMsg(msg) { showMessage(msg, "success"); }

function showMessage(msg, type) {
	$('#header').append('<div class="core_msg core_msg_'+type+'" onclick="$(this).fadeOut()"><p class="msg">'+msg+'</p></div>');
}

$(document).ready(function(){
    setTimeout(function(){
        $('#chat_wall').height($('#main_wall').height()-130);
        $('#main_wall_list').height($('#main_wall').height()-54);
        $('#player_wall').height($('#main_wall').height()-84);
        $('#chat_wall').animate({ scrollTop: $('#chat_wall').height()+1000 }, 1000);
    }, 100);
});

$('#post_comment').click(function(){
    $('#chat_form').hide();
    var newComment = $('#new_comment').val();
    $.ajax({
        type: 'POST',
        url: "/main/say?v="+Math.random(),
        data: { comment: newComment },
        dataType: "json"
    }).done(function(data){
        $('#chat_form').show();
        if (data.id > 0) {
            addToChat(data);
        }
    });
});

function addToChat(data)
{
    $('#chat_wall').append('<p><span>'+data.date+'</span><br>'+data.text+'</p>');
    $('#chat_wall').animate({ scrollTop: $('#chat_wall').height()+1000 }, 1000);
    $('#cw_last_id').val(data.id);
    $('#new_comment').val('');
}

$('#post_content').click(function(){
    $(this).hide();

    var newContent = $('#add_to_wall').val();
    var isYoutube = 0;

    if( !newContent.match(/http(s)?:/) || !newContent.match(/(\.jpg|\.jpeg|\.gif|\.ico|\.bmp|\.png|youtube\.com)/) ) {
        $('#post_content').show();
        return false;
    }
    if( newContent.match(/youtube\.com/) ) isYoutube = 1;
    if (isYoutube == 1) {
        newContent = newContent.replace(/watch\?v=/g, 'embed/');
    }

    $.ajax({
        type: 'POST',
        url: "/main/content?v="+Math.random(),
        data: { new_content: newContent, is_youtube: isYoutube },
        dataType: "json"
    }).done(function(data){
        $('#post_content').show();
        if (data.id > 0) {
            addToWall(data);
        }
    });
});

function addToWall(data)
{
    if (data.is_video == 0) {
        $('#main_wall_list').prepend('<p><span>'+data.date+'</span><br><img style="max-width: 100%" src="'+data.content+'" /></p>');
    } else {
        $('#main_wall_list').prepend('<p><span>'+data.date+'</span><br><iframe width="100%" height="300" src="'+data.content+'" frameborder="0" allowfullscreen wmode="Opaque"></iframe></p>');
    }
    $('#mw_last_id').val(data.id);
    $('#add_to_wall').val('');
}

var files;
$('#file_music').on('change', prepareUpload);

function prepareUpload(event)
{
    files = event.target.files;
    uploadFiles(event);
}

function uploadFiles(event)
{
    $('#file_music').hide();

    event.stopPropagation();
    event.preventDefault();

    var data = new FormData();
    $.each(files, function(key, value)
    {
        data.append('mp3_file', value);
    });

    $.ajax({
        url: "/main/upload_mp3?v="+Math.random(),
        type: 'POST',
        data: data,
        cache: false,
        dataType: 'json',
        processData: false,
        contentType: false
    }).done(function(data){
        $('#file_music').show();
        if (data.id > 0) {
            addMusic(data);
        }
    });
}

audiojs.events.ready(function() {
    var as = audiojs.createAll();
});

function addMusic(data)
{
    $('#player_wall').prepend('<p><span>'+data.name+'</span><br><audio src="'+data.path_name+'" preload="none" /></audio></p>');
    var as = audiojs.createAll();
    $('#muw_last_id').val(data.id);
    $('#file_music').val('');
}

function checkMainWallContent()
{
    $.ajax({
        url: "/main/check_main_wall?v="+Math.random(),
        type: 'POST',
        data: { last_id: $('#mw_last_id').val() },
        dataType: 'json'
    }).done(function(data){
        if (data.id > 0) {
            addToWall(data);
        }
        setTimeout(function(){ checkMainWallContent(); }, 5000);
    });
}
/**setTimeout(function(){ checkMainWallContent(); }, 5000);*/

function checkMusicWallContent()
{
    $.ajax({
        url: "/main/check_music_wall?v="+Math.random(),
        type: 'POST',
        data: { last_id: $('#muw_last_id').val() },
        dataType: 'json'
    }).done(function(data){
        if (data.id > 0) {
            $('#player_wall').prepend('<p><span>'+data.name+'</span><br><audio src="'+data.path_name+'" preload="none" /></audio></p>');
            var as = audiojs.createAll();
            $('#muw_last_id').val(data.id);
        }
        setTimeout(function(){ checkMusicWallContent(); }, 30000);
    });
}
/**setTimeout(function(){ checkMusicWallContent(); }, 30000);*/

function checkChatWallContent()
{
    $.ajax({
        url: "/main/check_chat_wall?v="+Math.random(),
        type: 'POST',
        data: { last_id: $('#cw_last_id').val() },
        dataType: 'json'
    }).done(function(data){
        if (data.id > 0) {
            addToChat(data);
        }
        setTimeout(function(){ checkChatWallContent(); }, 2000);
    });
}
/**setTimeout(function(){ checkChatWallContent(); }, 2000);*/

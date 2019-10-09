/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
const $ = require('jquery');
require('../css/app.scss');
require('pusher-js');
require('bootstrap');
require('js-cookie');
import Cookies from 'js-cookie'

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.

const pusher = new Pusher('f63a595c360996836b72', {
    cluster: 'eu',
    authEndpoint: '/auth-pusher'
});

console.log("Init");

let lastChannelName = null;

function joinMainChannels() {
    // Get  main channels
    console.log("joinMainChannels");
    let channels = getGeneralChannels();

    channels.forEach((channel, index) => {
        console.log("Adding channel " + channel);
        $("#salon-list ul").append("<li><button data-name=\"" + channel + "\" class='button-style-1'>" + channel + "</button></li>")
    });

    $("#salon-list ul li button").each(function (index) {
        $(this).click(function() {
            let channelName = $(this).data("name");
            $("#salon-title").html("Salon #" + channelName);

            let pusherChannelName = "presence-" + channelName;
            let channel = pusher.subscribe(pusherChannelName);

            $("#user-list ul li").remove();
            if (lastChannelName != null) {
                pusher.unsubscribe(lastChannelName);
            }

            lastChannelName = pusherChannelName;
            channel.bind('message', function (data) {
                $("#message-list").append("<li>" + data.message + "</li>");
            });

            channel.bind('pusher:subscription_succeeded', function(members) {
                // for example
                console.log("subscription_succeeded members.count = " + members.count);


                members.each(function(member) {
                    // for example:
                    console.log(member.id, member.info);
                    $("#user-list ul").append("<li id=\"member" + member.id + "\">" + member.info.name + "</li>");
                });
            });

            channel.bind('pusher:member_added', function(member) {
                console.log("Member added : " + member.id, member.info);
                $("#user-list ul").append("<li id=\"member" + member.id + "\">" + member.info.name + "</li>");
            });

            channel.bind('pusher:member_removed', function(member) {
                console.log("Member removed : " + member.id, member.info);
                $("#user-list #member" + member.id).remove();
            });

        });
    });

    // Join self private channel
    let userName = Cookies.get('user_name');
    let whisperChannelName = 'private-' + userName;
    let channelPrivateUser = pusher.subscribe(whisperChannelName);

    console.log("userName = " + userName);
    console.log("whisperChannelName = " + whisperChannelName);

    channelPrivateUser.bind('whisper', function(data) {
        console.log("channel private my-event = " + data.message);
    });
}

function getGeneralChannels() {
    return ["General", "Cat", "Dog", 'Android', 'Computer'];
}

function addMessage(messageType) {

}


$(document).ready(function () {
    $("#button-join").click(function () {
        let channelName = $("#input-button-join").val();


        if (channelName == null || channelName === "") {

        }
        else {
            console.log("Joining channel " + channelName);
            let channel = pusher.subscribe('presence-' + channelName);

            channel.bind('message', function(data) {

            });

            channel.bind('pusher:subscription_succeeded', function(members) {
                // for example
                console.log("subscription_succeeded members.count = " + members.count);

                members.each(function(member) {
                    // for example:
                    console.log(member.id, member.info);
                });
            });

            channel.bind('pusher:member_added', function(member) {
                console.log("Member added : " + member.id, member.info);
            });

            channel.bind('pusher:member_removed', function(member) {
                console.log("Member removed : " + member.id, member.info);
            });
        }

    });

    joinMainChannels();
});

//
// var channelPublicName = 'my-channel';
// var channelPublicName2 = 'my-channel2';
// var channelPrivateName = 'presence-chan';
// var channelPrivateName2 = 'private-chan';
//
// var channelPublic = pusher.subscribe(channelPublicName);
// var channelPublic2 = pusher.subscribe(channelPublicName2);
// var channelPrivate = pusher.subscribe(channelPrivateName);
// var channelPrivate2 = pusher.subscribe(channelPrivateName2);

// function sock() {
//
//     $.ajax({
//         type: "POST",
//         url: "/auth-pusher",
//         data: {
//             socket_id: pusher.connection.socket_id,
//             channel_name : channelPrivateName
//         },
//         success: function (data) {
//             console.log("SUCCESS : ", data);
//             let count = channelPrivate.members.count;
//             console.log("count : ", count);
//         },
//
//         error: function (e) {
//             console.log("ERROR : ", e);
//         }
//     });
// }
//
// // sock();
//
// channelPublic.bind('my-event', function(data) {
//     console.log("channel public my-event = " + data.message);
// });
//
// channelPrivate.bind('my-event', function(data) {
//     console.log("channel private my-event = " + data.message);
//     let count = channelPrivate.members.count;
//     console.log("count = " + count);
//     channelPrivate.members.each(function(member) {
//         console.log("member.id = " + member.id);
//         console.log("member.info.name = " + member.info.name);
//         console.log("member.info.email = " + member.info.email);
//     });
// });

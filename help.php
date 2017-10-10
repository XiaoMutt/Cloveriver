<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<?php
include_once '_basics.php';
CheckUser();
?>
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8">

        <title>Cloveriver</title>
        <link rel="icon" href="icons/clover.ico" />        
        <style type="text/css" title="currentStyle">
            @import "css/page.css";
            @import "css/jquery-ui-1.8.16.custom.css";
        </style>
        <script type="text/javascript" src="js/jquery.js"></script>
        <script type="text/javascript" src="js/jquery.dataTables.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.16.custom.min.js"></script>
        <script type="text/javascript" src="js/common.js"></script>
        <script type="text/javascript" charset="utf-8">
               
            $(document).ready(function(){
                $('.accordionhead').click(function() {
                    $(this).next().slideToggle(500);
                    return false;
                })
                
                $("#messenger_dialog").hide();                
            });

        </script>
    </head>
    <body>
        <?php
        Menu();
        ?>
        <div id="container">
            <div class="accordion">
                <div class="ui-widget-header  ui-corner-all accordionhead">Tips</div>
                <div class="text ui-widget-content ui-corner-all accordionbody">
                    <div style="margin:5px">
                        <img src="icons/arrow_up.png"/>If click the head bar like above, its content may toggle (show/hide).<br/>
                        <img src="icons/bullet_star.png"/>Type in the "Search" input box at the right top corner of a table to quickly find what you are looking for.<br/>
                        <img src="icons/bullet_star.png"/>Click on a user's name to send an email to him/her.<br/>
                        <img src="icons/bullet_star.png"/>Go to "Roster" to find the person you want to contact.<br/>
                        <img src="icons/bullet_star.png"/>Cloveriver is not just for equipment. The equipment can be a room, or even a person who takes reservations.<br/>
                        <img src="icons/bullet_star.png"/>If you are admin or manager of equipment, click <img src="icons/email.png"> to generate email list that you can copy to your email client to use.<br/>
                        <img src="icons/bullet_star.png"/>Check the status of the equipment before you schedule it.<br/>
                        <img src="icons/bullet_star.png"/>Hover your mouse to an icon to check what it says, or you can check the following list.<br/>
                    </div>
                </div>   
            </div>    
            <div class="spacer"></div>
            <div class="accordion">
                <div class="ui-widget-header  ui-corner-all accordionhead">Meanings of Icons</div>
                <div class="text ui-widget-content ui-corner-all accordionbody">
                    <div style="margin: 5px">
                        <img src="icons/user_edit.png"/> Edit user Position of in the user group of an equipment.<br/>
                        <img src="icons/tag_blue_add.png"/> Reserve this equipment<br/>
                        <img src="icons/page_white_text_width.png"/> Details about this record/user/equipment<br/>
                        <img src="icons/page_white_edit.png"/> Edit this record/user/equipment<br/>
                        <img src="icons/key.png"/> Edit the password of this user<br/>
                        <img src="icons/exclamation.png"/> This status of this equipment is “broken”<br/>
                        <img src="icons/email.png"/> Generate email list that you can use in your email client<br/>
                        <img src="icons/delete.png"/> Remove this user from the user group of this equipment<br/>
                        <img src="icons/cross.png"/> Delete record/user/equipment<br/>
                        <img src="icons/comment.png"/> There is a description on this reservation<br/>
                        <img src="icons/cancel.png"/> Cloveriver has encountered errors<br/>
                        <img src="icons/bullet_error.png"/> Cloveriver does not function properly<br/>
                        <img src="icons/bug_error.png"/> The status of this equipment is problematic<br/>
                        <img src="icons/add.png"/> Add equipment/a user<br/>
                        <img src="icons/accept.png"/> The status of the equipment is normal or Cloveriver has processed your request<br/>
                        <img src="icons/user_go.png"/> Apply to the user group of this equipment   <br/>
                    </div>
                </div>
            </div>
            <div class="spacer"></div>
            <div class="accordion">
                <div class="ui-widget-header  ui-corner-all accordionhead">FAQs</div>
                <div class="text ui-widget-content ui-corner-all accordionbody">
                    <div style="margin: 5px">
                        <img src="icons/user_comment.png"/>I am a new user, but I cannot schedule the equipment I need. Why?<br/>
                        <img src="icons/bell.png"/>You need go to "Equipment" page, find the equipment you need, and click <img src="icons/user_go.png"/> to apply to the user group of that equipment.<br/>
                        <img src="icons/bullet_green.png"/>At this point you need to wait (or maybe contact the manager of the equipment which you can find in the "User Rights" page) for the manager to change your Position to member.<br/>
                        <img src="icons/bullet_green.png"/>Then the equipment will show on the "Scheduler" page, and everything will work.<br/>
                        <br/>
                        <img src="icons/user_comment.png"/>How can I add an equipment?<br/>
                        <img src="icons/bell.png"/>Only "admin" has the right to add and equipment<br/>
                        <img src="icons/bullet_green.png"/>Go to the "Equipment" page, click the <img src="icons/add.png"/> at the top of the table, and add the equipment.<br/>
                        <img src="icons/bullet_green.png"/>You need somebody (including you) apply to the user group of the new equipment,and make him/her/yourself a member/manager of that equipment user group<br/>
                        <img src="icons/bullet_green.png"/>Then that person can schedule the equipment<br/>
                        <br/>
                        <img src="icons/user_comment.png"/>What's the difference between "user", "member", "admin", and so on?<br/>
                        <img src="icons/bell.png"/>Cloveriver has three user types which are all together called "Identity" in the system.<br/>
                        <img src="icons/bullet_green.png"/>The three types of "Identities" are: "visitor", "user", and "admin". Visitors are the new user who just registered in Cloveriver, but has not been activated by the "admin". Users are normal users, and admins are the people have the highest power in the system. Admins can view/change/delete user or equipment information, so they should be honest responsible people.<br/>
                        <img src="icons/bullet_green.png"/>Each equipment has a user group, and only the member/manager can schedule that equipment. Each user group has three type of "Positions": "applicant", "member", and "manager". Applicants are the users who just applied to the user group but have not been approved by the manager. Members are just members, and managers are the ones in charge of managing the user group of that equipment.<br/>
                        <img src="icons/bullet_green.png"/>Cloveriver can have more than one admin in the system, and each equipment user group can have more than one manager.
                        <br/>
                        <img src="icons/user_comment.png"/>I am a manager of a equipment. How can I send an email to all the members in this equipment user group?<br/>
                        <img src="icons/bell.png"/>You cannot send email through Cloveriver, since we do not have a valid mail server in the Internet.<br/>
                        <img src="icons/bullet_green.png"/>However, Cloveriver will generate the email list of that equipment user group for you. The only thing you need to do is click the <img src="icons/email.png"/> in the "Actions" column of equipment list.<br/>
                        <br/>
                        <img src="icons/user_comment.png"/>Why this system is called Cloveriver?<br/>
                        <img src="icons/bell.png"/>Clover a simple Lab Information Management System developed by me (Xiao).<br/>
                        <img src="icons/bullet_green.png"/>Cloveriver belongs to that system but runs independently. The other part is called Clover Sundew<br/>
                        <img src="icons/bullet_green.png"/>The "river" in Cloveriver stands for Reserve Is Very Easy Right here, which means Cloveriver is a scheduling system.<br/>
                        <img src="icons/bullet_green.png"/>If you are interested in Clover Sundew, please let me know<br/>
                        <br/>
                        <img src="icons/user_comment.png"/>I accidentally deleted something. What should I do?<br/>
                        <img src="icons/bell.png"/>Nothing in Cloveriver will be actually deteled. They are just marked as deleted.<br/>
                        <img src="icons/bullet_green.png"/>If you are an admin/manager and accidentally deleted a user or an equipment, please find me (Xiao). Information can be recovered.<br/>
                    </div>
                </div>
            </div>
            <div class="spacer"></div>
            © Xiao Zhou, 2012            
            <div class="spacer"></div>
        </div>
    </body>
</html>        
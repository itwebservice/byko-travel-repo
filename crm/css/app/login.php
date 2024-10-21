<?php
include_once('../../model/model.php');
header("Content-type: text/css");

global $theme_color, $theme_color_dark, $theme_color_2, $topbar_color, $sidebar_color;
?>

html,body{
margin:0;
padding:0;
}
.login-banner {
position: relative;
min-height: 50vh;
clip-path: polygon(0 0, 100% 0%, 100% 70%, 50% 100%, 0 70%);
overflow: hidden;
background-image: url(../../images/login/login-back.jpg);
background-position: bottom;
background-size: cover;
}
.login-banner:before {
content: "";
width: 100%;
height: 100%;
background-color: rgba(71, 88, 132, 0.9);
position: absolute;
top: 0;
left: 0;
}
.login-banner-icon {
position: absolute;
height: 60px;
width: 60px;
object-fit: contain;
}
.banner-plane-img {
left: 0;
top: 5%;
animation: planeMove 15s infinite;
}
.banner-passenger-img {
left: 28%;
top: 20%;
animation: UpDown 5s infinite;
}
.banner-holiday-img {
left: 20%;
bottom: 30%;
animation: swim 5s infinite;
}
.banner-sail-img {
right: 6%;
top: 20%;
animation: swim 5s infinite;
}
.banner-suitcases-img {
right: 25%;
top: 35%;
animation: leftRightsuitcases 5s infinite;
}
.banner-travel-img {
right: 14%;
top: 54%;
animation: swim 5s infinite;
}
.banner-bus-img {
bottom: 40%;
left: 2%;
animation: leftRight 5s infinite;
}
/* .login_screen{
background: url(../../images/login_bg.jpg);
background-size: cover;
background-repeat: no-repeat;
position: relative;
height: 100vh;
}
.login_screen:before {
position: absolute;
content: '';
width: 25%;
height: 100%;
background: rgba(0, 0, 0, 0.7);
left: 0;
top: 0;
} */
.login_wrap{
background: #fff;
width: 450px;
left: 50%;
top: 50%;
margin: 0 auto;
/* margin-top: -175px;*/
/*margin-left: -225px;*/
position: absolute;
padding-top: 70px;
box-shadow: 2px 0px 5px 2px rgba(0, 0, 0, 0.28);
transform: translate(-50%, -50%);
}
.login_wrap h3 {
margin: 0;
position: absolute;
top: 35px;
left: 30px;
font-size: 18px;
color: #475884;
font-weight: 500 !important;
}
/*.login_wrap h3{
margin: 0;
position: absolute;
top: 35px;
left: 45px;
font-size: 21px;
color: #444;
font-weight: 300 !important;
}*/
.logo-wrap{
height: 140px;
width: 140px;
border-radius: 50%;
right:30px;
top:-70px;
background: #fff;
position: absolute;
display:flex;
box-shadow: 2px 3px 2px #D7D7D7;
z-index: 1
}
.logo-wrap img{
max-width: 100%;
height: auto;
margin:auto;
}
.login_wrap_inner{
padding: 20px 30px;
background: #fff;
z-index: 1;
position: relative;
margin-top: 3px;
}
.div_version{
padding:9px 10px;
background: <?= $theme_color ?>;
color: #fff;
text-align: center;
font-size: 19px;
border-bottom-left-radius: 5px;
border-bottom-right-radius: 5px;
}
.btn, .form-control{
border-radius:4px;
}

label.error{
color: #a94442;
width: 100%;
line-height: 34px;
margin-left: 8px;
position: relative;
}

.login_wrap button {
width: 100%;
height: 42px;
}
input, select{height: 42px !important;}

.login-password-field {
position: relative;
}

.login-password-field .btn {
position: absolute;
top: 0;
right: 0;
height: 100%;
display: flex;
align-items: center;
justify-content: center;
}
.form-label {
font-size: 14px;
margin-bottom: 8px;
font-weight: 500;
color: #212529;
}

@keyframes planeMove {
0% {
}
100% {

left: 100%;
}
}
@keyframes swim {
0% {
transform: rotate(0deg);
}
25% {
transform: rotate(8deg);
}
75% {
transform: rotate(0deg);
}
100% {
transform: rotate(8deg);
}
}
@keyframes leftRight {
0% {
left: 2%;
}
25% {
left: 5%;
}
75% {
left: 8%;
}
100% {
left: 5%;
}
}
@keyframes leftRightsuitcases {
0% {
right: 25%;
}
25% {
right: 28%;
}
75% {
right: 31%;
}
100% {
right: 28%;
}
}
@keyframes UpDown {
0% {
top: 20%;
}
25% {
top: 23%;
}
75% {
top: 26%;
}
100% {
top: 23%;
}
}

@media screen and (max-width:992px){
.login_wrap{
margin-left: -125px;
}
}

@media screen and (max-width:768px){
.login_wrap{
padding-top: 102px;
min-width: 84%;
width: auto;
margin-top: 0;
margin-left: 170px;
top: 365px;
left: 8%;
}
.login_wrap_inner {
padding: 20px 25px;
}
.login_wrap h3 {
top: 80px;
}
}
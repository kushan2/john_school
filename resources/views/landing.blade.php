<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suny Chat | Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400&display=swap" rel="stylesheet">
    
<style>

body { 
    background: #F5F5F5; 
}
   
   
#landing {
  height:600px;
  width:400px;
  position: fixed;
  top: 48%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  justify-content: center; /* Horizontal centering */
  align-items: flex-end;     /* Vertical centering */
  background-image: url("../public/images/background.png");
  background-repeat: no-repeat;
  background-size: contain;
  border-radius: 30px;
}
        

.btn { 
    padding: 18px; 
    border-radius: 20px; 
    background: #0f4c81; 
    color: #fff; 
    font-size: 16px; 
    font-weight: 900; 
    text-decoration: none;
    
    
}
.btn:hover { opacity: .88; }     
        


#terms {
  width:400px;
  position: absolute;
  bottom: 0%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  justify-content: center; /* Horizontal centering */
  align-items: flex-end;     /*
}







</style>
</head>




<body>

<div id="landing">
<a href="{{ route('user.login') }}" class="btn btn-primary">
CHAT & CONNECT NOW</a>
</div>

<div id="terms"
<p><a href="{{ route('pages.terms') }}"> SUNY.CHAT Terms of Service</a></p>
</div>




</body>
</html>

function    onLoad(data) {
    console.log(data);
    let test = JSON.parse(data);
    $(".widget-body").html(data);
}

$(document).ready(function (){
    $.ajax({
        url: "http://localhost/API-laplateforme/index.php/students?id=&firstname=&lastname=",
        method: "get",
        datatype: "json",
        success: onLoad
    });
});
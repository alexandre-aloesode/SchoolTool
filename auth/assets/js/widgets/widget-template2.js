function onLoad2(data) {
  console.log(data);
  let test = JSON.parse(data);
  $('.widget-body2').html(data);
}

$(document).ready(function () {
  $.ajax({
    url: 'http://localhost/API-laplateforme/index.php/jobs?id_jobs=&name=',
    method: 'get',
    datatype: 'json',
    success: onLoad2,
  });
});

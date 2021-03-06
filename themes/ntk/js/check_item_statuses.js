/*global VuFind */

function checkItemStatuses(container) {
  if (typeof(container) == 'undefined') {
    container = $('body');
  }

  var elements = {};
  var data = $.map(container.find('.result'), function(record) { // zmena prvku, kvuli zmene css pozicovani
    if ($(record).find('.hiddenId').length == 0) {
      return null;
    }
    var datum = $(record).find('.hiddenId').val();
    if (typeof elements[datum] === 'undefined') {
        elements[datum] = $();
    }
    elements[datum] = elements[datum].add($(record));
    return datum;
  });
  if (!data.length) {
    return;
  }

  $(".ajax-availability").removeClass('hidden');
  $.ajax({
    dataType: 'json',
    method: 'POST',
    url: VuFind.path + '/AJAX/JSON?method=getItemStatuses',
    data: {'id':data}
  })
  .done(function(response) {
    $.each(response.data, function(i, result) {
      var item = elements[result.id];
      if (!item) {
        return;
      }
      
      if (result.status == 'Requested' || result.status == 'Vyřizuje se'
          || result.status == 'Pravděp. ztráta' || result.status == 'Claimed missing'
          || result.status == 'Ve zpracování' || result.status == 'In process'
          || result.status == 'Objednáno' || result.status == 'On Order'){
          item.find('.status').empty().append("<span class='label label-danger'>"+result.status+"</span>");
      }else{
          item.find('.status').empty().append(result.availability_message);
      }
      if (typeof(result.full_status) != 'undefined'
        && result.full_status.length > 0
        && item.find('.callnumAndLocation').length > 0
      ) {
        // Full status mode is on -- display the HTML and hide extraneous junk:
        item.find('.callnumAndLocation').empty().append(result.full_status);
        item.find('.callnumber').addClass('hidden');
        item.find('.location').addClass('hidden');
        item.find('.hideIfDetailed').addClass('hidden');
        item.find('.status').addClass('hidden');
      } else if (typeof(result.missing_data) != 'undefined'
        && result.missing_data
      ) {
        // No data is available -- hide the entire status area:
        item.find('.callnumAndLocation').addClass('hidden');
        item.find('.status').addClass('hidden');
      } else if (result.locationList) {
        // We have multiple locations -- build appropriate HTML and hide unwanted labels:
        item.find('.callnumber').addClass('hidden');
        item.find('.hideIfDetailed').addClass('hidden');
        item.find('.location').addClass('hidden');
        var locationListHTML = "";
        for (var x=0; x<result.locationList.length; x++) {
          locationListHTML += '<div class="groupLocation">';
          if (result.locationList[x].availability) {
            locationListHTML += '<i class="fa fa-ok text-success"></i> <span class="text-success">'
              + result.locationList[x].location + '</span> ';
          } else if (typeof(result.locationList[x].status_unknown) !== 'undefined'
              && result.locationList[x].status_unknown
          ) {
            if (result.locationList[x].location) {
              locationListHTML += '<i class="fa fa-status-unknown text-warning"></i> <span class="text-warning">'
                + result.locationList[x].location + '</span> ';
            }
          } else {
            locationListHTML += '<i class="fa fa-remove text-danger"></i> <span class="text-danger"">'
              + result.locationList[x].location + '</span> ';
          }
          locationListHTML += '</div>';
          locationListHTML += '<div class="groupCallnumber">';
          locationListHTML += (result.locationList[x].callnumbers)
               ?  result.locationList[x].callnumbers : '';
          locationListHTML += '</div>';
        }
        item.find('.locationDetails').removeClass('hidden');
        item.find('.locationDetails').empty().append(locationListHTML);
      } else {
        // Default case -- load call number and location into appropriate containers:
        item.find('.callnumber').empty().append(result.callnumber+'<br/>');
        item.find('.location').empty().append(
          result.reserve == 'true'
          ? result.reserve_message
          : result.location
        );
        
        // links
            if (result.location == "V\u0160CHT \u00fastavy"){
                item.find('.location').unwrap();
                item.find('.location').empty().append("<a href='https://www.chemtk.cz/cs/82950-seznam-ustavnich-knihoven'>"+result.location+"</a>");
            } else if (result.location == "UCT departments"){
                item.find('.location').unwrap();
                item.find('.location').empty().append("<a href='https://www.chemtk.cz/en/82974-departmental-libraries'>"+result.location+"</a>");
            } else if (result.location.indexOf("3D") > 0){ // studovna casopisu
                item.find('.location').empty().append(result.location);
                item.find('#linkhref').attr('data-lightbox-href', '../periodicals.php');
            } else if (
                    (result.location == "Unknown") || (result.location == "Neznámo") ||
                    (result.location == "Sklad historického fondu") || (result.location == "Stack room of historical collection") ||
                    (result.location == "Trezor historického fondu") || (result.location == "Reading room of historical collection") ||
                    (result.location == "Badatelna historick&eacute;ho fondu") || (result.location == "Safe of historical collection") ||
                    (result.location == "Depozitář") || (result.location == "Depository") ||
                    (result.location == "Konzultační koutek, 2. NP") || (result.location == "Knowledge Navigation Corner, 2nd floor") ||
                    (result.location == "Více umístění") || (result.location == "Multiple Locations") ||
                    (result.location == "Sklad") || (result.location == "Stack room") ||
                    (result.location == "Volný výběr, nezařazeno") || (result.location == "Open stacks, uncategorized") ||
                    (result.location == "ÚOCHB ústav") || (result.location == "IOCB department") ||
                    (result.location == "Book news, 4th floor") || (result.location == "Novinky, 4. NP")
                    ){
                    item.find('.location').unwrap();
                    item.find('.location').empty().append(result.location);
            } else {
                item.find('.location').empty().append(result.location);
                item.find('#linkhref').attr('data-lightbox-href', '../map.php?lcc=map' + result.callnumber);
                    var title = result.location;
                    if(typeof title === "undefined") {
                        title = $(this).html();
                    }
                    var p,s,r,vysledek,title_desc;
                    if (title.indexOf('Shelf') >= 0){
                      p = 'floor';
                      s = 'section';
                      r = 'shelf';
                    }else{
                      p = 'patro';
                      s = 'sekce';
                      r = 'regál';
                    }
                    var patro = title.charAt(6);
                    title_desc = p+': '+patro;
                    var sekce = title.charAt(7);
                    title_desc += ', '+s+': '+sekce;
                    var regal = title.substr(8,3);
                    title_desc += ', '+r+': '+regal;
                    vysledek = title+' ('+title_desc+')';
                item.find('#linkhref').attr('data-lightbox-title', vysledek);
            }
      }
    });

    $(".ajax-availability").removeClass('ajax-availability');
  })
  .fail(function(response, textStatus) {
    $('.ajax-availability').empty();
    if (textStatus == 'abort' || typeof response.responseJSON === 'undefined') { return; }
    // display the error message on each of the ajax status place holder
    $('.ajax-availability').append(response.responseJSON.data).addClass('text-danger');
  });
}

$(document).ready(function() {
  checkItemStatuses();
});

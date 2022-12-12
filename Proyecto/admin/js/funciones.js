/* 
 * Scripts
 * Voluta Estudio
 */
/*VARIABLES GLOBALES */
var esperaLink = "<div class='spinner-border text-primary' role='status'><span class='sr-only'>Un momento por favor...</span></div>";
var esperaSmall = "<img src='img/espera.gif' width=20>";
var mensaje = "";
eliminaspam();
var geoDireccion = "";



function existe(valor) {
    if (valor == "" | valor == undefined | valor == null | valor == "(NULL)" | String(valor).trim() == "") {
        return false;
    } else {
        return true;
    }
}


function anuncio(mensaje) {
    $("#aviso").show();
    $("#mensaje").show();
    $("#mensaje").children().remove();
    $("#mensaje").append("<p>" + mensaje + "</p><button name=aceptar class='btn btn-success'>Aceptar</button>");
    //evento
    $("#mensaje button[name=aceptar]").click(function () {
        $("#aviso").toggle();
        $("#mensaje").toggle();
    });
}

function aviso(mensaje) {
    $("#aviso").show();
    $("#mensaje").show();
    $("#mensaje").children().remove();
    $("#mensaje").append("<p>" + mensaje + "</p><button name=aceptar class='btn btn-success'>Aceptar</button>");
    //evento
    $("#mensaje button[name=aceptar]").click(function () {
        $("#aviso").toggle();
        $("#mensaje").toggle();
    });
}

function avisoLink(mensaje, link) {
    $("#aviso").show();
    $("#mensaje").show();
    $("#marco").children().remove();
    $("#marco").append("<p>" + mensaje + "</p><button name=aceptar class='btn btn-secondary'>Aceptar</button><br>");
    //evento
    $("#mensaje button[name=aceptar]").click(function () {
        $("#aviso").toggle();
        $("#mensaje").toggle();
        location.href = link;
    });
}

function espera() {
    $("#aviso").show();
    $("#mensaje").show();
    $("#mensaje").children().remove();
    $("#mensaje").append("<p>" + esperaLink + "</p>");
}

function eliminaspam() {
    $("#aviso").hide();
    $("#mensaje").hide();
    $("#contenidoEmergente").hide();
}

function info(texto) {
    $("#aviso").show();
    $("#mensaje").show();
    $("#mensaje").children().remove();
    $("#mensaje").append("<p>" + texto + "</p>");
}


//FUNCIONES TABULATOR----------------------------------------------
 //custom max min header filter
 var minMaxFilterEditor = function (cell, onRendered, success, cancel, editorParams) {
    var end;
    var container = document.createElement("span");
    //create and style inputs
    var start = document.createElement("input");
    start.setAttribute("type", "date");
    start.setAttribute("placeholder", "Min");
    //start.setAttribute("min", 0);
    //start.setAttribute("max", 100);
    start.style.padding = "4px";
    start.style.width = "50%";
    start.style.boxSizing = "border-box";

    start.value = cell.getValue();

    function buildValues() {
      success({
        start: start.value,
        end: end.value,
      });
    }

    function keypress(e) {
      if (e.keyCode == 13) {
        buildValues();
      }

      if (e.keyCode == 27) {
        cancel();
      }
    }
    end = start.cloneNode();
    end.setAttribute("placeholder", "Max");
    start.addEventListener("change", buildValues);
    start.addEventListener("blur", buildValues);
    start.addEventListener("keydown", keypress);
    end.addEventListener("change", buildValues);
    end.addEventListener("blur", buildValues);
    end.addEventListener("keydown", keypress);
    container.appendChild(start);
    container.appendChild(end);
    return container;
  }

  
  //custom max min filter function
  function minMaxFilterFunction(headerValue, rowValue, rowData, filterParams) {
    //headerValue - the value of the header filter element
    //rowValue - the value of the column in this row
    //rowData - the data for the row being filtered
    //filterParams - params object passed to the headerFilterFuncParams property

    if (rowValue) {
      if (headerValue.start != "") {
        if (headerValue.end != "") {
          return rowValue >= headerValue.start && rowValue <= headerValue.end;
        } else {
          return rowValue >= headerValue.start;
        }
      } else {
        if (headerValue.end != "") {
          return rowValue <= headerValue.end;
        }
      }
    }

    return true; //must return a boolean, true if it passes the filter.
  }
  var dateEditor = function (cell, onRendered, success, cancel) {
    //cell - the cell component for the editable cell
    //onRendered - function to call when the editor has been rendered
    //success - function to call to pass the successfuly updated value to Tabulator
    //cancel - function to call to abort the edit and return to a normal cell

    //create and style input
    if(!existe(cell.getValue())){
      today = new Date();
      dd = today.getDate();
      mm = today.getMonth() + 1; //January is 0!
      yyyy = today.getFullYear();
      var cellValue = luxon.DateTime.fromFormat(dd+"-"+mm+"-"+yyyy, "dd/MM/yyyy").toFormat("yyyy-MM-dd");
    }else{
      var cellValue = luxon.DateTime.fromFormat(cell.getValue(), "dd/MM/yyyy").toFormat("yyyy-MM-dd");
    }
    
      input = document.createElement("input");

    input.setAttribute("type", "date");

    input.style.padding = "4px";
    input.style.width = "100%";
    input.style.boxSizing = "border-box";

    input.value = cellValue;

    onRendered(function () {
      input.focus();
      input.style.height = "100%";
    });

    function onChange() {
      if (input.value != cellValue) {
        success(input.value);
      } else {
        cancel();
      }
    }

    //submit new value on blur or change
    input.addEventListener("blur", onChange);

    //submit new value on enter
    input.addEventListener("keydown", function (e) {
      if (e.keyCode == 13) {
        onChange();
      }

      if (e.keyCode == 27) {
        cancel();
      }
    });

    return input;
  };

  function vistaPrevia(input,div) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      
      reader.onload = function (e) {
          $(div).attr('src', e.target.result);
      }
      
      reader.readAsDataURL(input.files[0]);
  }else{
    $(div).attr('src', 'img/empleados/generico.png');
 }
}

function minutosToHora (minutes) {
  var h = Math.floor(minutes / 60);
  var m = minutes % 60;
  h = h < 10 ? '0' + h : h;
  m = m < 10 ? '0' + m : m;
  return h + ':' + m;
}
function fromSeconds(seconds, showHours) {
    if (showHours) {
        var hours = Math.floor(seconds / 3600),
            seconds = seconds - hours * 3600;
    }
    var minutes = ("0" + Math.floor(seconds / 60)).slice(-2);
    var seconds = ("0" + parseInt(seconds % 60, 10)).slice(-2);

    if (showHours) {
        var timestring = hours + ":" + minutes + ":" + seconds;
    } else {
        var timestring = minutes + ":" + seconds;
    }
    return timestring;
}

function findGetParameter(parameterName) {
        var result = null,
            tmp = [];
        location.search
            .substr(1)
            .split("&")
            .forEach(function(item) {
                tmp = item.split("=");
                if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
            });
        return result;
}

function showSnackbar() {
    var x = document.getElementById("snackbar");
    //console.log(x);
    x.className = "show";
    setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
}

function getNumberTimingScreen(timing_screen){
    let ris = 0.0;
    let vet_timing = Array();
    substr = timing_screen.split(":");
    substr.forEach(element => {
        vet_timing.push(parseInt(element)); 
    });
    ris = vet_timing[0] * 60 + vet_timing[1] + vet_timing[2] / 1000;

    return ris;
}

function getStartTimingTrim(){
    timing = document.getElementById("timing_video");
    start_trim = document.getElementById("start_timing_trim");
    start_trim.value = timing.value;
    end_trim = document.getElementById("end_timing_trim");
    checkTrimTime(start_trim, end_trim);
}

function getEndTimingTrim(){
    timing = document.getElementById("timing_video");
    start_trim = document.getElementById("start_timing_trim");
    end_trim = document.getElementById("end_timing_trim");
    end_trim.value = timing.value;
    checkTrimTime(start_trim, end_trim);
}

function checkTrimTime(start_trim, end_trim){
    if (start_trim != '' && end_trim != ''){
        let st = getNumberTimingScreen(start_trim.value);
        let et = getNumberTimingScreen(end_trim.value);
        if (st > et){
            showSnackbar();
            disableTrim(true);
        }
        else{
            disableTrim(false);
        }
    }
    else{
        disableTrim(true);
    }
}

function disableTrim(disabled){
    document.getElementById("trim_video").disabled = disabled;
}

function showScreenArea(){
    let screen_area = document.getElementById('screen_area');
    let btn = document.getElementById('show_screen_area');
    if (screen_area.hidden == true){
        screen_area.hidden = false;
        btn.innerHTML = "Nascondi gli screenshot";
    }
    else{
        screen_area.hidden = true;
        btn.innerHTML = "Mostra gli screenshot";
    }
}

function showMarks(){
    let screen_area = document.getElementById('marks');
    let btn = document.getElementById('show_marks');
    if (screen_area.hidden == true){
        screen_area.hidden = false;
        btn.innerHTML = "Nascondi i segnaposti";
    }
    else{
        screen_area.hidden = true;
        btn.innerHTML = "Mostra i segnaposti";
    }
}
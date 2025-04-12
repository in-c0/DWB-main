<html>
<head>
  <div id="h">
</div>
<script language="javascript">
function clip() {

    // get the clipboard text

    var clipText = window.clipboardData.getData('Text');

    // split into rows

    clipRows = clipText.split(String.fromCharCode(13));

    // split rows into columns

    for (i=0; i<clipRows.length; i++) {
        clipRows[i] = clipRows[i].split(String.fromCharCode(9));
    }


    // write out in a table

    newTable = document.createElement("table")
    newTable.border = 1;
    for (i=0; i<clipRows.length - 1; i++) {

        newRow = newTable.insertRow();

        for (j=0; j<clipRows[i].length; j++) {
            newCell = newRow.insertCell();
            if (clipRows[i][j].length == 0) {
                newCell.innerText = ' ';
            }
            else {
                newCell.innerText = clipRows[i][j];
            }
        }
    }

    document.body.appendChild(newTable);
}
</script>
</head>
<body>
<input type="button" onclick="clip()">
</body>
</html>
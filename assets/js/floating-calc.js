(function() {
    if (localStorage.getItem('showFloatingCalc') !== 'true') return;

    // Create container
    const calc = document.createElement('div');
    calc.id = 'floating-calc-container';
    calc.innerHTML = Calculator.getHTML(true);
    document.body.appendChild(calc);

    // Initialize logic
    Calculator.init('f-calc-display', 'f-calc-history');
    Calculator.attachEvents('#floating-calc-container');

    // Close functionality
    document.getElementById('calc-close-btn').onclick = () => {
        calc.remove();
        localStorage.setItem('showFloatingCalc', 'false');
    };

    // Draggable functionality
    dragElement(calc);

    function dragElement(elmnt) {
        let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
        const header = document.getElementById("calc-drag-handle");
        header.onmousedown = dragMouseDown;

        function dragMouseDown(e) {
            e = e || window.event;
            e.preventDefault();
            pos3 = e.clientX;
            pos4 = e.clientY;
            document.onmouseup = closeDragElement;
            document.onmousemove = elementDrag;
        }

        function elementDrag(e) {
            e = e || window.event;
            e.preventDefault();
            pos1 = pos3 - e.clientX;
            pos2 = pos4 - e.clientY;
            pos3 = e.clientX;
            pos4 = e.clientY;
            elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
            elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
            elmnt.style.bottom = "auto";
            elmnt.style.right = "auto";
        }

        function closeDragElement() {
            document.onmouseup = null;
            document.onmousemove = null;
        }
    }
})();

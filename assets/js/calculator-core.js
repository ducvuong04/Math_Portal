const Calculator = {
    expression: "",
    lastAns: 0,
    currentMode: "COMP",
    isMenuOpen: false,
    
    modes: [
        { id: 1, name: "Calculate", icon: "calculate" },
        { id: 2, name: "Complex", icon: "psychology" },
        { id: 3, name: "Base-N", icon: "pin" },
        { id: 4, name: "Matrix", icon: "grid_view" },
        { id: 5, name: "Vector", icon: "navigation" },
        { id: 6, name: "Statistics", icon: "bar_chart" },
        { id: 7, name: "Distribution", icon: "show_chart" },
        { id: 8, name: "Table", icon: "table_chart" },
        { id: 9, name: "Equation", icon: "functions" }
    ],

    init: function(displayId, historyId) {
        this.display = document.getElementById(displayId);
        this.history = document.getElementById(historyId);
        
        window.addEventListener('storage', (e) => {
            if (e.key === 'calc_sync_data') {
                const data = JSON.parse(e.newValue);
                this.expression = data.expr;
                this.lastAns = data.ans;
                this.currentMode = data.mode || "COMP";
                this.updateDisplay(data.displayText);
                this.history.innerText = data.historyText;
            }
        });
    },

    insert: function(val) {
        if (this.display.innerText === "0" && !isNaN(val)) this.expression = "";
        if (val === 'Ans') val = this.lastAns;
        this.expression += val;
        this.updateDisplay();
        this.sync();
    },

    clearAll: function() {
        this.expression = "";
        this.updateDisplay("0");
        this.history.innerText = this.currentMode;
        this.sync();
    },

    backspace: function() {
        this.expression = this.expression.slice(0, -1);
        this.updateDisplay();
        this.sync();
    },

    updateDisplay: function(val) {
        if (this.display) {
            this.display.innerText = val || this.expression || "0";
        }
    },

    calculate: function() {
        try {
            this.history.innerText = this.expression + " =";
            let processed = this.expression
                .replace(/π/g, 'Math.PI')
                .replace(/e/g, 'Math.E')
                .replace(/√/g, 'Math.sqrt')
                .replace(/\^/g, '**')
                .replace(/sin\(/g, 'Math.sin(')
                .replace(/cos\(/g, 'Math.cos(')
                .replace(/tan\(/g, 'Math.tan(')
                .replace(/log\(/g, 'Math.log10(')
                .replace(/ln\(/g, 'Math.log(');
                
            let result = eval(processed);
            this.lastAns = result;
            this.expression = result.toString();
            this.updateDisplay();
            this.sync();
        } catch (e) {
            this.display.innerText = "Syntax Error";
            this.expression = "";
        }
    },

    toggleMenu: function(container) {
        this.isMenuOpen = !this.isMenuOpen;
        const menuOverlay = container.querySelector('.calc-menu-overlay');
        menuOverlay.style.display = this.isMenuOpen ? 'flex' : 'none';
    },

    setMode: function(modeName, container) {
        this.currentMode = modeName.toUpperCase();
        this.history.innerText = this.currentMode;
        this.toggleMenu(container);
        this.clearAll();
        this.sync();
    },

    sync: function() {
        const data = {
            expr: this.expression,
            ans: this.lastAns,
            mode: this.currentMode,
            displayText: this.display.innerText,
            historyText: this.history.innerText
        };
        localStorage.setItem('calc_sync_data', JSON.stringify(data));
    },

    getHTML: function(isFloating = false) {
        const prefix = isFloating ? 'f-' : '';
        let menuItems = this.modes.map(m => `
            <div class="menu-item" data-mode="${m.name}">
                <span class="material-icons-round">${m.icon}</span>
                <span>${m.id}:${m.name}</span>
            </div>
        `).join('');

        let html = `
            <div class="fx580">
                <div class="calc-menu-overlay" style="display:none;">
                    <div class="menu-grid">
                        ${menuItems}
                    </div>
                </div>

                ${isFloating ? `
                <div id="calc-drag-handle">
                    <span>CASIO fx-580VN X</span>
                    <button id="calc-close-btn">×</button>
                </div>` : ''}
                
                <div class="casio-header">
                    <span class="casio-logo">CASIO</span>
                    <span class="model-name">fx-580VN X ClassWiz</span>
                </div>

                <div class="lcd-screen">
                    <div class="lcd-top">
                        <span>D</span>
                        <span id="${prefix}calc-history">COMP</span>
                    </div>
                    <div class="lcd-main" id="${prefix}calc-display">0</div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 12px;">
                    <button class="f-key key-shift">SHIFT</button>
                    <button class="f-key key-alpha">ALPHA</button>
                    <button class="f-key btn-on" style="background:#222;">ON</button>
                    <button class="f-key btn-menu" style="background:#222;">MENU</button>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                    <div class="nav-pad" style="margin: 0;">
                        <div class="nav-circle">
                            <button class="nav-btn" style="grid-column: 2; grid-row: 1;"><span class="material-icons-round">expand_less</span></button>
                            <button class="nav-btn" style="grid-column: 1; grid-row: 2;"><span class="material-icons-round">chevron_left</span></button>
                            <button class="nav-btn" style="grid-column: 3; grid-row: 2;"><span class="material-icons-round">chevron_right</span></button>
                            <button class="nav-btn" style="grid-column: 2; grid-row: 3;"><span class="material-icons-round">expand_more</span></button>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        <button class="btn-ctrl" data-val="OPTN">OPTN</button>
                        <button class="btn-ctrl" data-val="calc">CALC</button>
                        <button class="btn-ctrl" data-val="Math.abs(">abs</button>
                        <button class="btn-ctrl" data-val="**(-1)">x⁻¹</button>
                    </div>
                </div>

                <div class="main-keys" style="grid-template-columns: repeat(6, 1fr); gap: 8px; margin-bottom: 15px;">
                    <button class="f-key" data-val="/">□/□</button>
                    <button class="f-key" data-val="√(">√</button>
                    <button class="f-key" data-val="**2">x²</button>
                    <button class="f-key" data-val="^">xⁿ</button>
                    <button class="f-key" data-val="log(">log</button>
                    <button class="f-key" data-val="ln(">ln</button>
                    <button class="f-key" data-val="(-)">(-)</button>
                    <button class="f-key" data-val=",,,">°'\"</button>
                    <button class="f-key" data-val="hyp">hyp</button>
                    <button class="f-key" data-val="sin(">sin</button>
                    <button class="f-key" data-val="cos(">cos</button>
                    <button class="f-key" data-val="tan(">tan</button>
                    <button class="f-key">RCL</button>
                    <button class="f-key">ENG</button>
                    <button class="f-key" data-val="(">(</button>
                    <button class="f-key" data-val=")">)</button>
                    <button class="f-key" data-val=",">S⇔D</button>
                    <button class="f-key" data-val="M+">M+</button>
                </div>

                <div class="main-keys" style="grid-template-columns: repeat(5, 1fr); gap: 10px;">
                    <button class="n-key" data-val="7">7</button>
                    <button class="n-key" data-val="8">8</button>
                    <button class="n-key" data-val="9">9</button>
                    <button class="n-key key-del" data-val="DEL">DEL</button>
                    <button class="n-key key-ac" data-val="AC">AC</button>
                    <button class="n-key" data-val="4">4</button>
                    <button class="n-key" data-val="5">5</button>
                    <button class="n-key" data-val="6">6</button>
                    <button class="n-key key-op" data-val="*">×</button>
                    <button class="n-key key-op" data-val="/">÷</button>
                    <button class="n-key" data-val="1">1</button>
                    <button class="n-key" data-val="2">2</button>
                    <button class="n-key" data-val="3">3</button>
                    <button class="n-key key-op" data-val="+">+</button>
                    <button class="n-key key-op" data-val="-">−</button>
                    <button class="n-key" data-val="0">0</button>
                    <button class="n-key" data-val=".">.</button>
                    <button class="n-key" data-val="10**">x10ˣ</button>
                    <button class="n-key key-op" data-val="Ans">Ans</button>
                    <button class="n-key btn-calc-eq" style="background:#38bdf8; color:#000;">=</button>
                </div>
                <div class="classwiz-text">NATURAL-V.P.A.M.</div>
            </div>
        `;
        return html;
    },

    attachEvents: function(containerSelector) {
        const container = document.querySelector(containerSelector);
        
        container.querySelectorAll('[data-val]').forEach(btn => {
            btn.onclick = () => {
                const val = btn.getAttribute('data-val');
                if (val === 'AC') this.clearAll();
                else if (val === 'DEL') this.backspace();
                else this.insert(val);
            };
        });

        container.querySelector('.btn-calc-eq').onclick = () => this.calculate();
        container.querySelector('.btn-on').onclick = () => this.clearAll();
        
        container.querySelector('.btn-menu').onclick = () => this.toggleMenu(container);
        
        container.querySelectorAll('.menu-item').forEach(item => {
            item.onclick = () => {
                this.setMode(item.getAttribute('data-mode'), container);
            };
        });
    }
};

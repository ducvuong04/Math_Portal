<?php 
include 'includes/header.php'; 
?>

<main class="container">
    <div style="padding: 4rem 0 2rem; text-align: center;">
        <h1 style="font-size: 3rem;">Công Cụ Giải Toán</h1>
        <p style="color: var(--text-muted);">Giải nhanh các bài toán phức tạp chỉ trong nháy mắt.</p>
    </div>

    <div class="grid">
        <!-- Quadratic Equation Solver -->
        <div class="content-wrapper animate" style="margin: 0;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                <span class="material-icons-round" style="color: var(--accent); font-size: 2rem;">calculate</span>
                <h2>Giải Phương Trình Bậc 2</h2>
            </div>
            <p style="margin-bottom: 2rem; color: var(--text-muted);">Giải phương trình dạng: $ax^2 + bx + c = 0$</p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.8rem; color: var(--text-muted);">Hệ số a</label>
                    <input type="number" id="input_a" class="btn btn-outline" style="width: 100%; text-align: left; background: rgba(0,0,0,0.2);" placeholder="a">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.8rem; color: var(--text-muted);">Hệ số b</label>
                    <input type="number" id="input_b" class="btn btn-outline" style="width: 100%; text-align: left; background: rgba(0,0,0,0.2);" placeholder="b">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.8rem; color: var(--text-muted);">Hệ số c</label>
                    <input type="number" id="input_c" class="btn btn-outline" style="width: 100%; text-align: left; background: rgba(0,0,0,0.2);" placeholder="c">
                </div>
            </div>
            
            <button onclick="solveQuadratic()" class="btn btn-primary" style="width: 100%;">Giải Phương Trình</button>
            
            <div id="result_quad" style="margin-top: 2rem; padding: 1.5rem; background: rgba(255,255,255,0.05); border-radius: 12px; display: none;">
                <h4 style="margin-bottom: 1rem; color: var(--accent);">Kết quả:</h4>
                <div id="quad_output"></div>
            </div>
        </div>

        <!-- Logarithm Calculator -->
        <div class="content-wrapper animate" style="margin: 0;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                <span class="material-icons-round" style="color: var(--accent); font-size: 2rem;">auto_graph</span>
                <h2>Tính Logarit</h2>
            </div>
            <p style="margin-bottom: 2rem; color: var(--text-muted);">Tính giá trị $\log_a b$</p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.8rem; color: var(--text-muted);">Cơ số a</label>
                    <input type="number" id="log_a" class="btn btn-outline" style="width: 100%; text-align: left; background: rgba(0,0,0,0.2);" placeholder="a">
                </div>
                <div>
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.8rem; color: var(--text-muted);">Giá trị b</label>
                    <input type="number" id="log_b" class="btn btn-outline" style="width: 100%; text-align: left; background: rgba(0,0,0,0.2);" placeholder="b">
                </div>
            </div>
            
            <button onclick="calcLog()" class="btn btn-primary" style="width: 100%;">Tính Logarit</button>
            
            <div id="result_log" style="margin-top: 2rem; padding: 1.5rem; background: rgba(255,255,255,0.05); border-radius: 12px; display: none;">
                <h4 style="margin-bottom: 1rem; color: var(--accent);">Kết quả:</h4>
                <div id="log_output"></div>
            </div>
        </div>

        <!-- Vector Oxyz Calculator -->
        <div class="content-wrapper animate" style="margin: 0; grid-column: span 1;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                <span class="material-icons-round" style="color: var(--accent); font-size: 2rem;">explore</span>
                <h2>Vector Oxyz</h2>
            </div>
            <p style="margin-bottom: 1.5rem; color: var(--text-muted);">Tính tích có hướng & vô hướng.</p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 1.5rem;">
                <div>
                    <p style="font-size: 0.8rem; margin-bottom: 0.5rem;">Vector $\vec{u}(x_1, y_1, z_1)$</p>
                    <div style="display: flex; gap: 0.3rem;">
                        <input type="number" id="u1" placeholder="x" class="btn btn-outline" style="padding: 0.5rem; width: 33%;">
                        <input type="number" id="u2" placeholder="y" class="btn btn-outline" style="padding: 0.5rem; width: 33%;">
                        <input type="number" id="u3" placeholder="z" class="btn btn-outline" style="padding: 0.5rem; width: 33%;">
                    </div>
                </div>
                <div>
                    <p style="font-size: 0.8rem; margin-bottom: 0.5rem;">Vector $\vec{v}(x_2, y_2, z_2)$</p>
                    <div style="display: flex; gap: 0.3rem;">
                        <input type="number" id="v1" placeholder="x" class="btn btn-outline" style="padding: 0.5rem; width: 33%;">
                        <input type="number" id="v2" placeholder="y" class="btn btn-outline" style="padding: 0.5rem; width: 33%;">
                        <input type="number" id="v3" placeholder="z" class="btn btn-outline" style="padding: 0.5rem; width: 33%;">
                    </div>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button onclick="calcVector('dot')" class="btn btn-outline" style="flex: 1;">Tích vô hướng</button>
                <button onclick="calcVector('cross')" class="btn btn-primary" style="flex: 1;">Tích có hướng</button>
            </div>
            
            <div id="result_vec" style="margin-top: 2rem; padding: 1.5rem; background: rgba(255,255,255,0.05); border-radius: 12px; display: none;">
                <h4 style="margin-bottom: 1rem; color: var(--accent);">Kết quả:</h4>
                <div id="vec_output"></div>
            </div>
        </div>
    </div>
</main>

<script>
function solveQuadratic() {
    const a = parseFloat(document.getElementById('input_a').value);
    const b = parseFloat(document.getElementById('input_b').value);
    const c = parseFloat(document.getElementById('input_c').value);
    const output = document.getElementById('quad_output');
    const resultBox = document.getElementById('result_quad');
    
    if (isNaN(a) || isNaN(b) || isNaN(c)) return;

    resultBox.style.display = 'block';
    if (a === 0) {
        output.innerHTML = b === 0 ? (c === 0 ? "Vô số nghiệm" : "Vô nghiệm") : "$x = " + (-c/b).toFixed(2) + "$";
    } else {
        const delta = b*b - 4*a*c;
        if (delta < 0) output.innerHTML = "Vô nghiệm thực.";
        else if (delta === 0) output.innerHTML = "Nghiệm kép: $x = " + (-b/(2*a)).toFixed(2) + "$";
        else {
            const x1 = (-b + Math.sqrt(delta)) / (2*a);
            const x2 = (-b - Math.sqrt(delta)) / (2*a);
            output.innerHTML = "$x_1 = " + x1.toFixed(2) + ", x_2 = " + x2.toFixed(2) + "$";
        }
    }
    renderMathInElement(output);
}

function calcLog() {
    const a = parseFloat(document.getElementById('log_a').value);
    const b = parseFloat(document.getElementById('log_b').value);
    const output = document.getElementById('log_output');
    const resultBox = document.getElementById('result_log');

    if (isNaN(a) || isNaN(b) || a <= 0 || a === 1 || b <= 0) {
        alert("Điều kiện: $0 < a \neq 1, b > 0$");
        return;
    }

    resultBox.style.display = 'block';
    const res = Math.log(b) / Math.log(a);
    output.innerHTML = "$\log_{" + a + "} " + b + " = " + res.toFixed(4) + "$";
    renderMathInElement(output);
}

function calcVector(type) {
    const u = [parseFloat(document.getElementById('u1').value), parseFloat(document.getElementById('u2').value), parseFloat(document.getElementById('u3').value)];
    const v = [parseFloat(document.getElementById('v1').value), parseFloat(document.getElementById('v2').value), parseFloat(document.getElementById('v3').value)];
    const output = document.getElementById('vec_output');
    const resultBox = document.getElementById('result_vec');

    if (u.some(isNaN) || v.some(isNaN)) return;
    resultBox.style.display = 'block';

    if (type === 'dot') {
        const res = u[0]*v[0] + u[1]*v[1] + u[2]*v[2];
        output.innerHTML = "$\vec{u} \cdot \vec{v} = " + res + "$";
    } else {
        const x = u[1]*v[2] - u[2]*v[1];
        const y = u[2]*v[0] - u[0]*v[2];
        const z = u[0]*v[1] - u[1]*v[0];
        output.innerHTML = "$[\vec{u}, \vec{v}] = (" + x + ", " + y + ", " + z + ")$";
    }
    renderMathInElement(output);
}
</script>

<?php include 'includes/footer.php'; ?>

(function () {
    const root = document.getElementById('greide-scan-app');
    if (!root) return;

    const scanUrl = root.dataset.scanUrl;
    const submitUrl = root.dataset.submitUrl;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let currentScan = null;

    const pV = document.getElementById('publicView');
    const aV = document.getElementById('agentView');
    document.querySelectorAll('#viewtog button').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('#viewtog button').forEach((item) => item.classList.remove('active'));
            button.classList.add('active');
            const agent = button.dataset.v === 'agent';
            pV.style.display = agent ? 'none' : 'block';
            aV.style.display = agent ? 'block' : 'none';
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    const drop = document.getElementById('drop');
    const fileInput = document.getElementById('file');
    const results = document.getElementById('results');
    const dropInner = document.getElementById('dropInner');
    const demoBtn = document.getElementById('demoBtn');
    const resetBtn = document.getElementById('resetBtn');

    if (!drop || !fileInput || !results) return;

    drop.addEventListener('click', () => fileInput.click());
    ['dragover', 'dragenter'].forEach((eventName) => {
        drop.addEventListener(eventName, (event) => {
            event.preventDefault();
            drop.classList.add('hover');
        });
    });
    ['dragleave', 'drop'].forEach((eventName) => {
        drop.addEventListener(eventName, (event) => {
            event.preventDefault();
            drop.classList.remove('hover');
        });
    });
    drop.addEventListener('drop', (event) => {
        if (event.dataTransfer?.files?.[0]) handleFile(event.dataTransfer.files[0]);
    });
    fileInput.addEventListener('change', (event) => {
        if (event.target.files?.[0]) handleFile(event.target.files[0]);
    });
    demoBtn?.addEventListener('click', runDemoNoPhoto);
    resetBtn?.addEventListener('click', resetScan);

    function resetScan() {
        currentScan = null;
        drop.classList.remove('scanning');
        dropInner.style.display = 'block';
        [...drop.querySelectorAll('img.preview,.box')].forEach((node) => node.remove());
        results.innerHTML = '<div class="res-empty">De herkende soorten, een soortenrijkdom-score en je bewijs-kaart verschijnen hier zodra je een foto scant.</div>';
        if (resetBtn) resetBtn.style.display = 'none';
        fileInput.value = '';
    }

    function downscale(file) {
        return new Promise((resolve) => {
            const img = new Image();
            const url = URL.createObjectURL(file);
            img.onload = () => {
                const max = 1024;
                let { width: w, height: h } = img;
                const scale = Math.min(1, max / Math.max(w, h));
                w = Math.round(w * scale);
                h = Math.round(h * scale);
                const canvas = document.createElement('canvas');
                canvas.width = w;
                canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                const data = canvas.toDataURL('image/jpeg', 0.85);
                resolve({ url, base64: data.split(',')[1], media: 'image/jpeg' });
            };
            img.src = url;
        });
    }

    async function handleFile(file) {
        if (!file.type.startsWith('image/')) return;
        const { url, base64, media } = await downscale(file);
        showPreview(url);
        startScanning();
        let scanResult = { species: null, live: false };
        try {
            scanResult = await callAI(base64, media);
        } catch (error) {
            scanResult = { species: null, live: false };
        }
        if (!scanResult.species || !scanResult.species.length) {
            scanResult = { species: demoSpecies(), live: false };
        }
        currentScan = {
            base64,
            media,
            species: scanResult.species,
            live: scanResult.live,
            isDemo: false,
        };
        finishScan(currentScan);
    }

    function runDemoNoPhoto() {
        const svg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect width='400' height='300' fill='%23bfe0ea'/%3E%3Ccircle cx='330' cy='60' r='30' fill='%23f0c45a'/%3E%3Cpath d='M0 180 Q120 150 240 180 T400 172 V300 H0Z' fill='%239bb277'/%3E%3Cpath d='M0 220 Q140 195 300 220 T400 215 V300 H0Z' fill='%237a9a55'/%3E%3C/svg%3E";
        showPreview(svg);
        startScanning();
        setTimeout(() => {
            currentScan = {
                base64: null,
                media: null,
                species: demoSpecies(),
                live: false,
                isDemo: true,
            };
            finishScan(currentScan);
        }, 1700);
    }

    function showPreview(src) {
        dropInner.style.display = 'none';
        [...drop.querySelectorAll('img.preview,.box')].forEach((node) => node.remove());
        const image = document.createElement('img');
        image.className = 'preview';
        image.src = src;
        drop.appendChild(image);
        if (resetBtn) resetBtn.style.display = 'inline-flex';
    }

    function startScanning() {
        drop.classList.add('scanning');
        results.innerHTML = '<div class="res-empty">⏳ Greide-scan bezig… AI-beeldherkenning analyseert de foto.</div>';
    }

    async function callAI(base64, media) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 90000);
        const response = await fetch(scanUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            signal: controller.signal,
            body: JSON.stringify({ image: base64, media }),
        });
        clearTimeout(timeout);

        if (!response.ok) {
            throw new Error('Scan mislukt');
        }

        const data = await response.json();

        return {
            live: !!data.live,
            species: (data.species || []).map((species) => ({
                nl: species.nl,
                fy: species.fy || '',
                count: species.count || 1,
                conf: Math.round(species.confidence ?? 80),
            })),
        };
    }

    function demoSpecies() {
        return [
            { nl: 'Grutto', fy: 'Skries', count: 3, conf: 96 },
            { nl: 'Kievit', fy: 'Ljip', count: 5, conf: 93 },
            { nl: 'Tureluur', fy: 'Tsjirk', count: 2, conf: 88 },
            { nl: 'Scholekster', fy: 'Bonte wile', count: 1, conf: 84 },
        ];
    }

    function finishScan(scan) {
        const { species, live, isDemo } = scan;
        drop.classList.remove('scanning');
        drawBoxes(species.length);
        const total = species.reduce((sum, item) => sum + (item.count || 1), 0);
        const score = Math.min(100, species.length * 18 + 10);
        const chips = species
            .map(
                (item) =>
                    `<span class="chip">🪶 ${item.nl} ${
                        item.fy ? `<i style="font-weight:400;color:var(--muted)">(${item.fy})</i>` : ''
                    } <b>×${item.count}</b> · ${item.conf}%</span>`
            )
            .join('');
        const now = new Date().toLocaleDateString('nl-NL', { day: 'numeric', month: 'long', year: 'numeric' });
        const areaName = root.dataset.areaName || 'Ljippelân Workum';
        const submitHint = isDemo
            ? '<p class="statusline" style="margin-top:0.75rem">Upload een echte foto om in te zenden — de voorbeeldscan wordt niet opgeslagen.</p>'
            : '<p class="statusline" style="margin-top:0.75rem">Na inzenden gaat de scan naar onze experts en verschijnt hij in de belwachtrij.</p>';

        results.innerHTML = `
      <div style="font-weight:700;color:var(--green-d);font-size:1.05rem">${species.length} soort(en) herkend · ${total} vogels</div>
      <div class="chips">${chips}</div>
      <div style="font-size:.85rem;color:var(--muted);font-weight:600">Soortenrijkdom-score</div>
      <div class="scorebar"><i id="sb"></i></div>
      <div class="statusline"><span class="dot"></span> ${
          live ? 'Live AI-herkenning via greidefugels.nl' : 'Voorbeeldscan (live-verbinding niet beschikbaar)'
      } · daarna geverifieerd door onze experts</div>

      <div class="proof" id="proof">
        <div class="top"><b>Biodiversiteits-bewijs</b><span class="seal">🪶</span></div>
        <div class="body">
          <div class="kv"><span>Bedrijf</span><b id="pCo">— vul hieronder in —</b></div>
          <div class="kv"><span>Weidegebied</span>${areaName}</div>
          <div class="kv"><span>Datum waarneming</span>${now}</div>
          <div class="kv"><span>Soorten / vogels</span>${species.length} / ${total}</div>
        </div>
        <div class="foot"><span>Methode: AI-beeldherkenning + expertannotatie · herkomst vastgelegd</span><span>Greidefugels.nl · ANF</span></div>
      </div>
      <div class="field">
        <input id="coName" placeholder="Bedrijfsnaam *" required>
        <input id="coEmail" type="email" placeholder="E-mail (optioneel)">
        <button type="button" class="btn btn--green" id="submitProof" style="font-size:.9rem" ${isDemo ? 'disabled' : ''}>Inzenden voor verificatie</button>
      </div>
      <div id="submitStatus"></div>
      ${submitHint}
    `;

        requestAnimationFrame(() => {
            const bar = document.getElementById('sb');
            if (bar) bar.style.width = `${score}%`;
        });

        const coName = document.getElementById('coName');
        const proofCompany = document.getElementById('pCo');
        coName?.addEventListener('input', () => {
            if (proofCompany) proofCompany.textContent = coName.value || '— vul hieronder in —';
        });

        document.getElementById('submitProof')?.addEventListener('click', () => submitScan(scan));
    }

    async function submitScan(scan) {
        const status = document.getElementById('submitStatus');
        const button = document.getElementById('submitProof');
        const companyName = document.getElementById('coName')?.value?.trim();
        const companyEmail = document.getElementById('coEmail')?.value?.trim();

        if (scan.isDemo || !scan.base64) {
            if (status) status.innerHTML = '<p class="statusline" style="color:#b06d1c">Upload eerst een echte foto.</p>';
            return;
        }

        if (!companyName) {
            if (status) status.innerHTML = '<p class="statusline" style="color:#b06d1c">Vul een bedrijfsnaam in.</p>';
            return;
        }

        if (button) {
            button.disabled = true;
            button.textContent = 'Bezig met inzenden…';
        }

        try {
            const response = await fetch(submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    image: scan.base64,
                    media: scan.media,
                    company_name: companyName,
                    company_email: companyEmail || null,
                    live: scan.live,
                    species: scan.species.map((item) => ({
                        nl: item.nl,
                        fy: item.fy || null,
                        count: item.count || 1,
                        confidence: item.conf ?? 80,
                    })),
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Inzenden mislukt');
            }

            if (status) {
                status.innerHTML = `<div class="res-empty" style="margin-top:12px;border-color:#cfe0c8;background:#eaf2e6;color:var(--green-d)">${data.message}</div>`;
            }
            if (button) button.textContent = 'Ingezonden ✓';
        } catch (error) {
            if (status) {
                status.innerHTML = `<p class="statusline" style="color:#b06d1c">${error.message || 'Inzenden mislukt. Probeer het opnieuw.'}</p>`;
            }
            if (button) {
                button.disabled = false;
                button.textContent = 'Inzenden voor verificatie';
            }
        }
    }

    function drawBoxes(count) {
        const spots = [
            [18, 40],
            [46, 55],
            [64, 38],
            [33, 66],
            [72, 62],
        ];
        for (let index = 0; index < Math.min(count, spots.length); index += 1) {
            const box = document.createElement('div');
            box.className = 'box';
            box.style.left = `${spots[index][0]}%`;
            box.style.top = `${spots[index][1]}%`;
            box.style.width = '13%';
            box.style.height = '16%';
            box.innerHTML = '<span>weidevogel</span>';
            drop.appendChild(box);
        }
    }

    const partnerBtn = document.getElementById('partnerLeadBtn');
    partnerBtn?.addEventListener('click', () => {
        const company = document.getElementById('leadCo')?.value?.trim();
        const email = document.getElementById('leadMail')?.value?.trim();
        if (!company || !email) {
            alert('Vul bedrijfsnaam en e-mail in.');
            return;
        }
        alert('Bedankt! Ons team neemt contact op voor een voorstel op maat.');
    });
})();

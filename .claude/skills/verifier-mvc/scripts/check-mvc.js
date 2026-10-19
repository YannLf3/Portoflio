#!/usr/bin/env node
/**
 * Outil de vérification automatique de la séparation MVC (Front-End & Back-End).
 * 
 * Contrôles Front-End (JavaScript) :
 *   1. M ne fait jamais référence à V (V. interdit dans M) ;
 *   2. V ne fait jamais référence à M (M. interdit dans V) ;
 *   3. Seul V accède au DOM (document., window. interdits dans M et C) ;
 *   4. Détection du respect des blocs et méthodes M, V et C.
 * 
 * Contrôles Back-End (PHP) :
 *   5. Interdiction absolue de la boucle foreach dans app/ (remplacement par array_map, array_filter, etc.) ;
 *   6. Présence obligatoire de declare(strict_types=1);.
 * 
 * Code de sortie : 0 si tout est conforme, 1 si au moins une violation.
 */

const fs = require('fs');
const path = require('path');

const args = process.argv.slice(2);

if (args.includes('--help') || args.includes('-h')) {
  console.log(`Usage : node check-mvc.js [options] [fichier-ou-dossier]

Arguments :
  fichier-ou-dossier   Fichier JS/PHP spécifique ou dossier (par défaut: tout le projet)

Options :
  --js-only            Vérifier uniquement les fichiers JavaScript front-end
  --php-only           Vérifier uniquement les fichiers PHP back-end
  --help, -h           Afficher cette aide

Exemples :
  node check-mvc.js
  node check-mvc.js public/js/terminal.js
  node check-mvc.js --js-only
  node check-mvc.js --php-only`);
  process.exit(0);
}

const jsOnly = args.includes('--js-only');
const phpOnly = args.includes('--php-only');
const targetArg = args.find(a => !a.startsWith('--'));

// Racine du projet
const projectRoot = path.resolve(__dirname, '..', '..', '..');
const targetPath = targetArg ? path.resolve(process.cwd(), targetArg) : projectRoot;

/**
 * Retire les commentaires et chaînes de caractères pour éviter les faux positifs.
 * @param {string} code
 * @returns {string}
 */
function stripCommentsAndStrings(code) {
  return code
    .replace(/\/\*[\s\S]*?\*\//g, m => m.replace(/[^\n]/g, ' '))
    .replace(/\/\/[^\n]*/g, m => ' '.repeat(m.length))
    .replace(/(['"`])(?:\\.|(?!\1)[^\\\n])*\1/g, m => ' '.repeat(m.length));
}

/**
 * Liste récursivement les fichiers selon une extension.
 * @param {string} dir
 * @param {string} ext
 * @returns {string[]}
 */
function getFiles(dir, ext) {
  if (!fs.existsSync(dir)) return [];
  const stat = fs.statSync(dir);
  if (stat.isFile()) return dir.endsWith(ext) ? [dir] : [];

  let results = [];
  const list = fs.readdirSync(dir);
  for (const file of list) {
    if (file === 'node_modules' || file === '.git' || file === 'vendor' || file === '.agents') continue;
    const fullPath = path.join(dir, file);
    const subStat = fs.statSync(fullPath);
    if (subStat && subStat.isDirectory()) {
      results = results.concat(getFiles(fullPath, ext));
    } else if (file.endsWith(ext)) {
      results.push(fullPath);
    }
  }
  return results;
}

const violations = [];

// ==========================================
// 1. CONTRÔLE FRONT-END JAVASCRIPT
// ==========================================
if (!phpOnly) {
  let jsFiles = [];
  if (fs.existsSync(targetPath) && fs.statSync(targetPath).isFile() && targetPath.endsWith('.js')) {
    jsFiles = [targetPath];
  } else {
    const jsDir = path.join(projectRoot, 'public', 'js');
    jsFiles = getFiles(jsDir, '.js');
  }

  for (const file of jsFiles) {
    const relFile = path.relative(projectRoot, file);

    const source = fs.readFileSync(file, 'utf8');
    const rawLines = source.split('\n');
    const strippedLines = stripCommentsAndStrings(source).split('\n');

    let owner = null; // 'M', 'V' ou 'C'
    let ownerDepth = 0;
    let currentDepth = 0;

    strippedLines.forEach((line, i) => {
      // Détection de déclaration de composant : const M = { ou M.methode = function
      const objectDecl = line.match(/^\s*(?:const|let|var)\s+([MVC])\s*=\s*\{/);
      const funcDecl = line.match(/^\s*(?:const|let|var)?\s*([MVC])\.\w+\s*=\s*(?:function|\([^)]*\)\s*=>|\w+\s*=>|\{)/);

      if (!owner) {
        if (objectDecl) {
          owner = objectDecl[1];
          ownerDepth = currentDepth;
        } else if (funcDecl) {
          owner = funcDecl[1];
          ownerDepth = currentDepth;
        }
      }

      // Suivi de la profondeur des accolades
      const openBraces = (line.match(/\{/g) || []).length;
      const closeBraces = (line.match(/\}/g) || []).length;

      // Règle 1 & 2 : M ne référence jamais V, et V ne référence jamais M
      const forbidden = owner === 'M' ? 'V' : owner === 'V' ? 'M' : null;
      if (forbidden && new RegExp(`\\b${forbidden}\\.`).test(line)) {
        violations.push({
          type: 'JS_MVC_LEAK',
          file: relFile,
          line: i + 1,
          message: `${owner} fait référence à ${forbidden} (étanchéité rompue : seul C peut faire le lien)`,
          code: rawLines[i].trim()
        });
      }

      // Règle 3 : dans M et C, aucun accès direct au DOM (document, window)
      // Seul V a le droit de manipuler le DOM
      if ((owner === 'M' || owner === 'C') && /\b(document|window)\./.test(line)) {
        violations.push({
          type: 'JS_DOM_ACCESS',
          file: relFile,
          line: i + 1,
          message: `${owner} accède directement au DOM (${line.match(/\b(document|window)\./)[0]} réservé à V)`,
          code: rawLines[i].trim()
        });
      }

      currentDepth += openBraces - closeBraces;
      if (owner && currentDepth <= ownerDepth) {
        owner = null;
      }
    });
  }
}

// ==========================================
// 2. CONTRÔLE BACK-END PHP
// ==========================================
if (!jsOnly) {
  let phpFiles = [];
  if (fs.existsSync(targetPath) && fs.statSync(targetPath).isFile() && targetPath.endsWith('.php')) {
    phpFiles = [targetPath];
  } else {
    const appDir = path.join(projectRoot, 'app');
    phpFiles = getFiles(appDir, '.php');
  }

  for (const file of phpFiles) {
    const relFile = path.relative(projectRoot, file);
    // Les vues (templates HTML) sont exclues des règles PHP strictes
    const isView = relFile.includes('app/views/');

    const source = fs.readFileSync(file, 'utf8');
    const rawLines = source.split('\n');
    const strippedLines = stripCommentsAndStrings(source).split('\n');

    if (!isView) {
      // Règle 5 : Interdiction absolue de foreach dans les contrôleurs et modèles
      strippedLines.forEach((line, i) => {
        if (/\bforeach\s*\(/.test(line)) {
          violations.push({
            type: 'PHP_FOREACH_BANNED',
            file: relFile,
            line: i + 1,
            message: `Boucle 'foreach' interdite par AGENTS.md (remplacer par array_map, array_filter, etc.)`,
            code: rawLines[i].trim()
          });
        }
      });

      // Règle 6 : declare(strict_types=1);
      if (!source.includes('declare(strict_types=1);')) {
        violations.push({
          type: 'PHP_STRICT_TYPES',
          file: relFile,
          line: 1,
          message: `Absence de 'declare(strict_types=1);' en tête de fichier`,
          code: rawLines[0] || ''
        });
      }
    }
  }
}

// ==========================================
// RAPPORT FINAL
// ==========================================
console.log('\n========================================');
console.log('   RAPPORT DE CONFORMITÉ MVC PORTFOLIO  ');
console.log('========================================\n');

if (violations.length === 0) {
  console.log('✅ SUCCÈS : Aucune violation détectée.');
  console.log('   - Séparation stricte M/V/C respectée dans le JavaScript.');
  console.log('   - Aucun accès direct au DOM dans M ou C (réservé à V).');
  console.log('   - Aucune boucle foreach détectée dans le Back-End PHP.');
  console.log('   - Typage strict PHP actif.\n');
  process.exit(0);
}

console.log(`❌ ÉCHEC : ${violations.length} violation(s) détectée(s) :\n`);

const grouped = violations.reduce((acc, v) => {
  acc[v.file] = acc[v.file] || [];
  acc[v.file].push(v);
  return acc;
}, {});

for (const [file, list] of Object.entries(grouped)) {
  console.log(`📄 [${file}] :`);
  list.forEach(v => {
    console.log(`   Ligne ${v.line} [${v.type}] : ${v.message}`);
    if (v.code) console.log(`   > ${v.code}`);
  });
  console.log('');
}

console.log('Actions correctives recommandées :');
console.log(' - Si M appelle V ou V appelle M : déplacer l\'orchestration dans C.handler_... ;');
console.log(' - Si M ou C accède au DOM : déplacer la manipulation dans une fonction de V ;');
console.log(' - Si un foreach PHP est détecté : le convertir avec array_map(), array_filter() ou un itérateur.\n');

process.exit(1);

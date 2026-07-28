# EasySchool ERP — Render.com এ Deploy করার গাইড (Free Tier)

এই গাইডটি ধাপে ধাপে দেখাবে কীভাবে আপনার Laravel 12 স্কুল ERP প্রজেক্ট (`c:\easyschool-erp`) সম্পূর্ণ ফ্রি-তে Render.com-এ live করবেন।

**শুরু করার আগে জেনে রাখুন (আগে থেকেই তৈরি করা আছে):**

- আপনার প্রজেক্ট ইতিমধ্যে একটি local Git repository (`git init` + প্রথম commit করা হয়ে গেছে) — কিন্তু এখনও GitHub-এ push করা হয়নি।
- Deployment হবে Docker দিয়ে — প্রজেক্টে ইতিমধ্যে `Dockerfile`, `docker/entrypoint.sh`, `docker/nginx.conf.template`, `docker/supervisord.conf` ফাইলগুলো তৈরি করা আছে। একটি মাত্র container-এ nginx + php-fpm + queue worker + scheduler একসাথে চলবে।
- রুট ফোল্ডারে একটি `render.yaml` (Render "Blueprint") ফাইল আছে, যেটা ব্যবহার করে Render নিজে থেকেই পুরো সার্ভিস তৈরি করে নেবে।
- একটি `.env.render.example` ফাইল আছে যেখানে ঠিক কোন কোন environment variable Render-এর dashboard-এ বসাতে হবে তার তালিকা দেওয়া আছে (যেগুলোর পাশে `CHANGE_ME` লেখা আছে সেগুলো secret/instance-specific মান, নিজে বসাতে হবে)।

আপনাকে শুধু নিচের ৭টি ধাপ ক্রমানুসারে অনুসরণ করতে হবে।

---

## ধাপ ১: স্থানীয়ভাবে APP_KEY তৈরি করুন

Render-এ deploy করার আগেই একটি APP_KEY লাগবে, কারণ Laravel এই key দিয়ে session/cookie/password ইত্যাদি encrypt করে।

1. আপনার প্রজেক্ট ফোল্ডারে যান — `c:\easyschool-erp`।
2. টার্মিনাল/PowerShell খুলে এই কমান্ডটি চালান:

   ```
   php artisan key:generate --show
   ```

3. এই কমান্ডটি একটি লাইন প্রিন্ট করবে, যেমন:

   ```
   base64:XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX=
   ```

   **লক্ষ্য করুন:** `--show` ফ্ল্যাগ থাকায় এটি শুধু screen-এ দেখাবে, আপনার local `.env` ফাইল পরিবর্তন করবে না — তাই নিশ্চিন্তে চালাতে পারেন।

4. পুরো লাইনটি (`base64:` সহ) copy করে একটি Notepad ফাইলে বা কোনো নিরাপদ জায়গায় সংরক্ষণ করুন। এটি ধাপ ৪-এ Render dashboard-এ `APP_KEY` হিসেবে বসাতে হবে।

---

## ধাপ ২: GitHub-এ কোড push করা

আপনার local repository-টি এখন GitHub-এ তুলতে হবে, কারণ Render সরাসরি GitHub repository থেকেই কোড টেনে নিয়ে deploy করে।

### ২.১ GitHub অ্যাকাউন্ট তৈরি

1. [https://github.com](https://github.com) এ যান, **Sign up** করুন।
2. শুধু একটি ইমেইল দিয়েই অ্যাকাউন্ট খোলা যায় — কোনো card লাগবে না।

### ২.২ একটি নতুন খালি (empty) repository তৈরি করুন

1. লগইন করার পর উপরে ডানদিকে **+** আইকনে ক্লিক করে **New repository** নির্বাচন করুন।
2. একটি নাম দিন, যেমন `easyschool-erp`।
3. **গুরুত্বপূর্ণ:** "Add a README file", "Add .gitignore", "Choose a license" — এই তিনটি অপশনের **কোনোটিই টিক দেবেন না**। Repository-টি সম্পূর্ণ খালি রাখতে হবে, কারণ আপনার local repo-তে আগে থেকেই commit আছে; GitHub নিজে থেকে কোনো ফাইল তৈরি করলে push করার সময় conflict হবে।
4. **Create repository** ক্লিক করুন।

### ২.৩ কোড push করুন — দুটি পদ্ধতি

**অপশন A — কমান্ড লাইন (git) দিয়ে:**

Repository তৈরি হওয়ার পর GitHub একটি URL দেখাবে, যেমন `https://github.com/<username>/<repo>.git`। এখন `c:\easyschool-erp` ফোল্ডারে গিয়ে নিচের কমান্ডগুলো একে একে চালান (**`<username>` এবং `<repo>` এর জায়গায় আপনার আসল GitHub username এবং repository-এর নাম বসান**):

```
git remote add origin https://github.com/<username>/<repo>.git
git branch -M main
git push -u origin main
```

`git push` চালানোর সময় যদি username/password চায়:

- **Username** — আপনার GitHub username।
- **Password** — এখানে আপনার সাধারণ GitHub account password কাজ করবে **না**। GitHub এখন সরাসরি password দিয়ে git push করা বন্ধ করে দিয়েছে। এর বদলে একটি **Personal Access Token (PAT)** লাগবে। PAT তৈরি করতে: GitHub-এ **Settings → Developer settings → Personal access tokens → Tokens (classic) → Generate new token**, `repo` scope-টি টিক দিন, তারপর **Generate token** ক্লিক করে যে টোকেন পাবেন সেটাই password-এর জায়গায় paste করুন।

**অপশন B — GitHub Desktop (command line ছাড়া, সহজ পদ্ধতি):**

যদি টার্মিনালে কমান্ড চালাতে স্বচ্ছন্দ না হন, তাহলে:

1. [desktop.github.com](https://desktop.github.com) থেকে **GitHub Desktop** ইনস্টল করুন।
2. আপনার GitHub অ্যাকাউন্ট দিয়ে sign in করুন।
3. **Add local repository** দিয়ে `c:\easyschool-erp` ফোল্ডারটি select করুন।
4. **Publish repository** বাটনে ক্লিক করুন — GitHub Desktop নিজে থেকেই login/authentication সামলে নেবে, আলাদা করে token লাগবে না।

দুটি পদ্ধতির যেকোনো একটি ব্যবহার করলেই চলবে — ফলাফল একই, কোড GitHub-এ push হয়ে যাবে।

---

## ধাপ ৩: db4free.net-এ ফ্রি MySQL ডাটাবেস বানানো

Render-এর free plan-এ built-in ডাটাবেস নেই, তাই একটি external free MySQL ব্যবহার করা হচ্ছে — **db4free.net**, কারণ এটি ব্যবহার করতে কোনো credit card লাগে না।

> **মনে রাখবেন:** db4free.net শুধুমাত্র টেস্টিং/ডেমো-এর জন্য উপযুক্ত। এর storage quota খুবই ছোট (প্রায় ২০০MB) এবং uptime-এর কোনো নিশ্চয়তা (SLA) নেই। আসল স্কুলের ছাত্র/অভিভাবকের ডেটা দীর্ঘমেয়াদে এখানে রাখা উচিত নয়।

> **গুরুত্বপূর্ণ — MySQL 8.0 আবশ্যক:** এই প্রজেক্টের একটি migration (`Modules/Attendance`) একটি MySQL 8.0.13+ "functional index" ব্যবহার করে (`COALESCE(subject_id, 0)` দিয়ে তৈরি), যা MySQL 5.7-এ কাজ করে না। খুশির খবর হলো db4free.net এখন ডিফল্টভাবে **MySQL 8.0** সার্ভারেই নতুন account তৈরি করে (5.7 বন্ধ/deprecated হয়ে গেছে) — তাই সাধারণ সাইনআপেই এটি এমনিতেই ঠিক থাকবে। শুধু সাইনআপের সময় ভুলেও কোনো পুরনো "MySQL 5.7" instance/লিংক বেছে নেবেন না; যদি db4free ছাড়া অন্য কোনো free MySQL provider ব্যবহার করেন, সেখানে MySQL ভার্সন কমপক্ষে 8.0.13+ কিনা যাচাই করে নেবেন — নাহলে `php artisan migrate --force` ধাপে deploy fail করবে।

1. [https://www.db4free.net](https://www.db4free.net) এ যান।
2. **Sign up** পেজে যান এবং পূরণ করুন:
   - Database name (নিজের পছন্দমতো একটি নাম দিন, যেমন `easyschool_db`)
   - Username
   - Password
   - Email
3. এই তিনটি মান (database name, username, password) হুবহু (case-sensitive) মনে রাখুন বা লিখে রাখুন — ধাপ ৪-এ লাগবে।
4. Sign up করার পর সাধারণত একটি **ইমেইল confirmation** পাঠানো হয় — ইমেইলে গিয়ে confirmation লিংকে ক্লিক করুন।
5. Confirmation করার পরেও ডাটাবেসটি সম্পূর্ণভাবে চালু (active) হতে কিছু সময় (কয়েক মিনিট) লাগতে পারে — সাথে সাথে কাজ না করলে একটু অপেক্ষা করুন।

এই তথ্যগুলো সরাসরি Render-এর environment variable-এর সাথে মিলে যাবে:

| db4free.net তথ্য | Render Environment Variable |
|---|---|
| Hostname (সাধারণত `db4free.net`) | `DB_HOST` |
| Port `3306` | `DB_PORT` |
| আপনার দেওয়া database name | `DB_DATABASE` |
| আপনার দেওয়া username | `DB_USERNAME` |
| আপনার দেওয়া password | `DB_PASSWORD` |

এই মানগুলোও ধাপ ১-এর APP_KEY-এর সাথে একই নোটে লিখে রাখুন।

---

## ধাপ ৪: Render.com অ্যাকাউন্ট বানানো ও Blueprint দিয়ে deploy করা

1. [https://render.com](https://render.com) এ যান এবং **Sign Up** করুন। সবচেয়ে সহজ উপায় হলো **"Sign up with GitHub"** — এতে GitHub অ্যাকাউন্টের সাথে সরাসরি যুক্ত হয়ে যাবে। Free tier-এর জন্য কোনো card লাগে না।
2. লগইন করার পর Dashboard-এ উপরে ডানদিকে **"New +"** বাটনে ক্লিক করুন।
3. মেনু থেকে **"Blueprint"** নির্বাচন করুন — **"Web Service" নয়**। কারণ প্রজেক্টের রুটে থাকা `render.yaml` ফাইলটিই পুরো সার্ভিস আগে থেকে সংজ্ঞায়িত করে রেখেছে; "Blueprint" বেছে নিলে Render নিজে থেকেই সেই ফাইল পড়ে সব সেটিংস বসিয়ে নেবে।
4. GitHub অ্যাকাউন্ট connect/authorize করতে বললে অনুমতি দিন, এবং ধাপ ২-এ push করা repository-টি (যেমন `easyschool-erp`) নির্বাচন করুন।
5. Render স্বয়ংক্রিয়ভাবে `render.yaml` খুঁজে বের করে `easyschool-erp` নামে একটি Docker-ভিত্তিক web service (free plan) দেখাবে।
6. **Apply** বা **Deploy Blueprint** (Render-এর ভাষায় বাটনের নাম সামান্য ভিন্ন হতে পারে) ক্লিক করুন।
7. এখন Render আপনাকে `sync: false` হিসেবে চিহ্নিত environment variable-গুলো পূরণ করতে বলবে। এগুলো হলো:
   - `APP_KEY` → ধাপ ১-এ কপি করা `base64:...` মানটি বসান।
   - `APP_URL` → এই মুহূর্তে খালি রাখুন বা যেকোনো placeholder দিন (যেমন `https://placeholder.onrender.com`), কারণ Render সার্ভিস তৈরি হওয়ার **পরেই** আসল URL দেবে। এটি একটু পরে ঠিক করতে হবে (নিচে দেখুন)।
   - `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` → ধাপ ৩-এ db4free.net থেকে পাওয়া মানগুলো বসান।
   - যদি `.env.render.example` ফাইলে Stripe-সংক্রান্ত কোনো চাবি (যেমন `STRIPE_KEY`, `STRIPE_SECRET`) `sync: false` হিসেবে থাকে, সেগুলোও একইভাবে নিজের মান দিয়ে পূরণ করুন।
8. সব পূরণ করে চূড়ান্ত **Create**/**Apply** বাটনে ক্লিক করুন। Render এখন কোড টেনে নিয়ে Docker image build করে deploy শুরু করবে — প্রথমবার build হতে (dependency install + build) সাধারণত ৫–১০ মিনিট বা তার বেশি সময় লাগতে পারে।
9. Build শেষ হয়ে সার্ভিস তৈরি হলে, সার্ভিস পেজের উপরে Render একটি আসল URL দেখাবে, যেমন `https://easyschool-erp-xxxx.onrender.com`। এই URL-টি copy করুন।

### APP_URL আপডেট করা (প্রথম deploy-এর পর অবশ্যই করতে হবে)

1. সার্ভিস পেজে **"Environment"** ট্যাবে যান।
2. `APP_URL` variable-টি খুঁজে বের করে, তার মান আগের placeholder থেকে বদলে আসল assigned URL (যেমন `https://easyschool-erp-xxxx.onrender.com`, শেষে `/` ছাড়া) দিন।
3. **Save Changes** ক্লিক করুন।
4. এতে সাধারণত স্বয়ংক্রিয়ভাবে redeploy শুরু হয়ে যায়; যদি না হয়, তাহলে নিজে থেকে **"Manual Deploy" → "Deploy latest commit"** ক্লিক করুন। এই redeploy না করলে সঠিক `APP_URL` Laravel-এ কার্যকর হবে না (asset link, CSRF ইত্যাদির জন্য জরুরি)।

---

## ধাপ ৫: প্রথম deploy এর পর যাচাই

1. Render dashboard-এ সার্ভিসটি খুলুন এবং **"Logs"** (বা **"Events"**) ট্যাবে যান — এখানে build ও deploy-এর সবকিছু live দেখা যাবে।
2. Log-এ যা দেখলে বুঝবেন সব ঠিকমতো চলছে:
   - `composer install` সফলভাবে শেষ হওয়া
   - `artisan migrate` কোনো fatal database error ছাড়া চলা
   - `supervisord` দিয়ে nginx, php-fpm, queue worker, scheduler প্রসেসগুলো চালু হওয়া
   - সবশেষে সার্ভিস পেজের উপরে সবুজ **"Live"** status/badge দেখা যাওয়া
3. যদি build/deploy fail করে, Log-এ লাল রঙের error লাইনগুলো পড়ুন — সাধারণ কারণ হতে পারে ভুল database credentials (db4free.net-এ connect করতে না পারা) অথবা ভুল/অনুপস্থিত `APP_KEY`।
4. সবকিছু ঠিক থাকলে ব্রাউজারে গিয়ে দেখুন:

   ```
   https://<আপনার-সার্ভিসের-আসল-নাম>.onrender.com/up
   ```

   Laravel 12-এর built-in health check route (`/up`) সফলভাবে লোড হলে বুঝবেন deployment সঠিকভাবে সম্পন্ন হয়েছে। এরপর অ্যাপের মূল login পেজ খুলেও একবার test করে দেখুন।

---

## ধাপ ৬: UptimeRobot দিয়ে সবসময় সচল রাখা

Render-এর free web service ডিফল্টভাবে "always-on" থাকে না — প্রায় ১৫ মিনিট কোনো ট্রাফিক না পেলে এটি ঘুমিয়ে (spin down) যায়, এবং তারপর কেউ visit করলে সেটি আবার জাগতে (cold start) প্রায় ৩০–৫০ সেকেন্ড সময় নেয়।

এটি এড়ানোর একটি বাস্তবসম্মত (practical) উপায় হলো **UptimeRobot** নামের ফ্রি সার্ভিস দিয়ে প্রতি ৫ মিনিটে একবার নিজের সার্ভিসে ping পাঠানো, যাতে সার্ভিস কখনো সম্পূর্ণ idle না হয়।

1. [https://uptimerobot.com](https://uptimerobot.com) এ গিয়ে ফ্রি **Sign Up** করুন — এখানেও কোনো card লাগে না।
2. ইমেইল verify করে dashboard-এ ঢুকুন।
3. **"+ Add New Monitor"** ক্লিক করুন।
4. **Monitor Type** থেকে **HTTP(s)** বেছে নিন।
5. **Friendly Name** এ যেকোনো নাম দিন, যেমন "EasySchool ERP"।
6. **URL** ফিল্ডে আপনার আসল Render URL-এর health check path বসান:

   ```
   https://<আপনার-সার্ভিসের-আসল-নাম>.onrender.com/up
   ```

7. **Monitoring Interval** থেকে **5 minutes** নির্বাচন করুন (ফ্রি প্ল্যানে সবচেয়ে কম ব্যবধান এটাই)।
8. **Create Monitor** ক্লিক করুন।

এরপর থেকে UptimeRobot প্রতি ৫ মিনিটে আপনার সার্ভিসে ping পাঠাবে, ফলে বাস্তবে সার্ভিসটি প্রায় সবসময় জাগ্রত থাকবে।

> **মনে রাখবেন:** এটি একটি চতুর workaround মাত্র, Render-এর অফিসিয়াল কোনো গ্যারান্টি নয় — maintenance, নতুন deploy, বা অন্য কোনো কারণে Render মাঝেমধ্যে সার্ভিস restart/spin down করতেই পারে।

---

## ধাপ ৭: সীমাবদ্ধতা (স্পষ্টভাবে জানিয়ে রাখা)

এই সেটআপ ব্যবহারের আগে নিচের সীমাবদ্ধতাগুলো স্পষ্টভাবে জেনে রাখুন:

- **db4free.net শুধু টেস্টিং/ডেমোর জন্য** — এর storage quota খুবই ছোট (প্রায় ২০০MB) এবং uptime-এর কোনো নিশ্চয়তা (SLA) দেওয়া নেই। যেকোনো সময় ডাটাবেস বন্ধ থাকতে পারে বা ডেটা হারাতে পারে।
- **আপলোড করা ফাইল (documents, ছবি ইত্যাদি) স্থায়ী নয়** — Render-এর free tier-এ কোনো persistent disk/volume নেই (ephemeral disk)। তাই প্রতিবার সার্ভিস restart বা redeploy হলে container-এ আপলোড করা সব ফাইল মুছে যাবে।
- **Cold start হতে পারে** — UptimeRobot-এর ping কোনো কারণে miss হলে, বা Render maintenance-এর জন্য সার্ভিস বন্ধ থাকলে, প্রথম ভিজিটরকে ৩০–৫০ সেকেন্ড অপেক্ষা করতে হতে পারে।
- **এই পুরো সেটআপ একটি সাময়িক/ডেমো deployment** — এটি প্রকৃত স্কুলের ছাত্র/অভিভাবকের ডেটা দীর্ঘমেয়াদে রাখার জন্য উপযুক্ত production সমাধান নয়। বাস্তব ব্যবহারের জন্য পরবর্তীতে paid database, persistent storage, এবং paid/always-on hosting প্ল্যানে migrate করা প্রয়োজন হবে।

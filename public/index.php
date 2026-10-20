<?php

$pageTitle = "Student Collaboration Hub";
$pageDescription = "Connect with students, discover project teams with skill matching, barter skills, vote on ideas, and attend campus workshops.";

require_once __DIR__ . "/../includes/header.php";

?>

<main>

    <!-- ====================================================================
         SECTION 1: Cinematic Cosmic Hero with Canvas & 3D Synergy Core
         ==================================================================== -->
    <section class="hero-cosmic" style="position: relative; overflow: hidden; padding: 70px 0 90px;">
        <!-- Interactive Celestial Starlight Canvas -->
        <canvas id="cosmicCanvas" class="cosmic-canvas" aria-hidden="true"></canvas>

        <!-- Ambient Atmospheric Planet Glow & Halo -->
        <div style="position: absolute; top: -140px; right: -100px; width: 520px; height: 520px; border-radius: 50%; background: radial-gradient(circle at 35% 35%, rgba(56, 189, 248, 0.28) 0%, rgba(99, 102, 241, 0.2) 45%, transparent 70%); filter: blur(40px); pointer-events: none;" aria-hidden="true"></div>
        <div style="position: absolute; bottom: -100px; left: -80px; width: 420px; height: 420px; border-radius: 50%; background: radial-gradient(circle at 50% 50%, rgba(168, 85, 247, 0.22) 0%, transparent 65%); filter: blur(50px); pointer-events: none;" aria-hidden="true"></div>

        <div class="container" style="position: relative; z-index: 2;">

            <!-- Campus Nexus Official Innovation Hub Banner -->
            <div style="text-align: center; margin-bottom: 28px;">
                <div class="hero-institution-banner animate-fade-up">
                    <img src="<?= htmlspecialchars($baseUrl) ?>/images/cn-logo.png" alt="Campus Nexus Logo" class="hero-inst-img">
                    <span class="hero-inst-text">Campus Nexus · Student Innovation & Collaboration Network</span>
                </div>
            </div>

            <!-- Centered Hero Layout -->
            <div class="hero-content animate-fade-up" style="max-width: 860px; margin: 0 auto; text-align: center;">
                <div style="margin-bottom: 18px;">
                    <span class="kicker-label kicker-cosmic">
                        ✦ The Central Student Collaboration Platform
                    </span>
                </div>

                <h1 class="font-display" style="font-size: clamp(2.6rem, 5.5vw, 4.4rem); line-height: 1.12; margin: 0 0 22px;">
                    Your Campus. <br>
                    Your Ideas. <span class="text-gradient-cosmic">Your Future.</span>
                </h1>

                <p class="hero-description" style="font-size: 1.18rem; max-width: 720px; margin: 0 auto 36px; color: var(--cosmic-text-muted); line-height: 1.75;">
                    Connect across campus departments to discover cross-functional hackathon teams,
                    exchange peer skills, pitch campus initiatives, and attend workshops — all in one unified, gamified ecosystem.
                </p>

                <div class="hero-actions" style="display: flex; align-items: center; justify-content: center; gap: 16px; flex-wrap: wrap; margin-bottom: 44px;">
                    <?php if ($isLoggedIn): ?>
                        <a class="btn btn-glow" style="padding: 14px 32px; font-size: 1.05rem;" href="<?= htmlspecialchars($baseUrl) ?>/public/dashboard.php">
                            Go to Your Dashboard
                        </a>
                        <a class="btn btn-cosmic-outline" style="padding: 14px 28px; font-size: 1.05rem;" href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/team-finder.php">
                            Browse Projects
                        </a>
                    <?php else: ?>
                        <a class="btn btn-glow" style="padding: 14px 34px; font-size: 1.05rem;" href="<?= htmlspecialchars($baseUrl) ?>/modules/auth/register.php">
                            Start Collaborating
                        </a>
                        <a class="btn btn-cosmic-outline" style="padding: 14px 28px; font-size: 1.05rem;" href="#features">
                            Explore Features
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Feature Metric Highlights -->
                <div style="display: flex; gap: 28px; justify-content: center; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.4rem;">🎯</span>
                        <div style="text-align: left;">
                            <strong style="color: white; font-size: 0.95rem; display: block;">100% Free & Open</strong>
                            <span style="font-size: 0.78rem; color: var(--cosmic-text-muted);">Campus-Wide Network</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.4rem;">🤝</span>
                        <div style="text-align: left;">
                            <strong style="color: white; font-size: 0.95rem; display: block;">Smart Skill Match</strong>
                            <span style="font-size: 0.78rem; color: var(--cosmic-text-muted);">Cross-Domain Teaming</span>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 1.4rem;">🏆</span>
                        <div style="text-align: left;">
                            <strong style="color: white; font-size: 0.95rem; display: block;">Gamified XP & Badges</strong>
                            <span style="font-size: 0.78rem; color: var(--cosmic-text-muted);">Verified Achievements</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ====================================================================
         SECTION 1B: Live Campus Activity Stream ("Nexus Pulse")
         ==================================================================== -->
    <div class="nexus-pulse-bar" aria-label="Campus Activity Ticker">
        <div class="pulse-inner">
            <span class="pulse-label"><span class="pulse-dot"></span> Nexus Live Pulse</span>
            <div class="ticker-viewport">
                <div class="ticker-track">
                    <span class="ticker-item">🚀 <strong>Parth Patel (Computer Engg)</strong> launched <em>Campus AI Rover</em> project</span>
                    <span class="ticker-item">🤝 <strong>Ananya Shah</strong> accepted <em>Python & React</em> peer mentorship</span>
                    <span class="ticker-item">💡 <strong>Dev Mehta</strong> proposed <em>Green Campus Smart Waste IoT</em> (24 votes)</span>
                    <span class="ticker-item">🏆 <strong>Sneha Prajapati</strong> unlocked <em>Team Builder</em> badge (+30 pts)</span>
                    <span class="ticker-item">📅 <strong>Workshop:</strong> <em>Full-Stack Web Innovation Summit</em> 12 seats left</span>
                    <span class="ticker-item">⚡ <strong>Rohit Joshi</strong> offered <em>Machine Learning</em> guidance on Skill Exchange</span>
                    <span class="ticker-item">🚀 <strong>Parth Patel (Computer Engg)</strong> launched <em>Campus AI Rover</em> project</span>
                    <span class="ticker-item">🤝 <strong>Ananya Shah</strong> accepted <em>Python & React</em> peer mentorship</span>
                    <span class="ticker-item">💡 <strong>Dev Mehta</strong> proposed <em>Green Campus Smart Waste IoT</em> (24 votes)</span>
                </div>
            </div>
        </div>
    </div>


    <!-- ====================================================================
         SECTION 2: Problem Statement & Campus Nexus Solution
         ==================================================================== -->
    <section class="section-cream bg-grid-peach" style="padding: 85px 0; border-bottom: 1px solid var(--border);">
        <div class="container">

            <div style="text-align: center; max-width: 720px; margin: 0 auto 50px;" class="reveal-on-scroll">
                <span class="kicker-label kicker-peach">The Campus Challenge</span>
                <h2 class="font-display text-gradient-peach" style="font-size: clamp(2rem, 3.8vw, 2.7rem); margin: 12px 0 16px;">
                    Great Ideas Stumble Without the Right Team
                </h2>
                <p style="color: var(--text-secondary); font-size: 1.05rem; line-height: 1.7;">
                    Campus project recruiting is usually locked behind scattered WhatsApp groups, department silos, and disorganized flyers. Here is how Campus Nexus transforms student collaboration.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(310px, 1fr)); gap: 24px;" class="reveal-on-scroll">

                <!-- The Old Way -->
                <div class="card-interactive" style="border-left: 4px solid var(--danger); background: white;">
                    <span style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--danger);">The Fragmented Past</span>
                    <h3 style="font-family: var(--font-display); margin: 8px 0 14px;">Scattered & Inefficient</h3>
                    <ul style="padding-left: 20px; color: var(--muted); margin: 0; line-height: 1.8; font-size: 0.95rem;">
                        <li>Posting in 10 different WhatsApp groups hoping someone knows Python.</li>
                        <li>No way to verify peer skill proficiencies or previous project experiences.</li>
                        <li>Unstructured meetings that fizzle out before a single commit is made.</li>
                        <li>Campus workshops announced too late with limited seat visibility.</li>
                    </ul>
                </div>

                <!-- The Nexus Way -->
                <div class="card-interactive card-peach" style="border-left: 4px solid var(--primary); background: var(--surface-peach);">
                    <span style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--primary);">The Nexus Solution</span>
                    <h3 style="font-family: var(--font-display); margin: 8px 0 14px; color: var(--text-primary);">Unified & Algorithmic</h3>
                    <ul style="padding-left: 20px; color: var(--text-secondary); margin: 0; line-height: 1.8; font-size: 0.95rem;">
                        <li><strong>Automated Match Scoring:</strong> Instantly check how your registered skills match project requirements.</li>
                        <li><strong>Structured Skill Bartering:</strong> Trade what you know for what you want to learn.</li>
                        <li><strong>Transparent Idea Board:</strong> Validate and vote on proposals with anti-duplicate safeguards.</li>
                        <li><strong>Real-time Workshop Seats:</strong> Instant seat registration with live availability tracking.</li>
                    </ul>
                </div>

            </div>

        </div>
    </section>


    <!-- ====================================================================
         SECTION 3: Core Feature Ecosystem
         ==================================================================== -->
    <section id="features" style="padding: 90px 0; background: white; border-bottom: 1px solid var(--border);">
        <div class="container">

            <div style="text-align: center; max-width: 700px; margin: 0 auto 52px;" class="reveal-on-scroll">
                <span class="kicker-label kicker-peach">Core Modules</span>
                <h2 class="font-display text-gradient-peach" style="font-size: clamp(2.1rem, 4vw, 2.8rem); margin: 12px 0 16px;">
                    Everything You Need to Build Together
                </h2>
                <p style="color: var(--muted); font-size: 1.05rem;">
                    Six dedicated tools built to turn individual student curiosity into working team achievements.
                </p>
            </div>

            <div class="card-grid reveal-on-scroll">

                <article class="card-interactive card-peach">
                    <div style="font-size: 2.3rem; margin-bottom: 12px;" class="animate-float">🚀</div>
                    <h3 style="margin-top: 0; font-family: var(--font-display); color: var(--text-primary);">Project Team Finder</h3>
                    <p style="color: var(--muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 18px;">
                        Explore student projects with clear descriptions, creators, and automated percentage compatibility meters based on your registered skills.
                    </p>
                    <a class="nav-link" style="padding-left: 0; font-weight: 700; color: var(--primary);" href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/team-finder.php">
                        Find Open Teams &rarr;
                    </a>
                </article>

                <article class="card-interactive card-peach">
                    <div style="font-size: 2.3rem; margin-bottom: 12px;" class="animate-float-delayed">🤝</div>
                    <h3 style="margin-top: 0; font-family: var(--font-display); color: var(--text-primary);">Skill Exchange</h3>
                    <p style="color: var(--muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 18px;">
                        Publish offers for skills you excel at and request guidance from student peers in programming, UI design, robotics, or data analysis.
                    </p>
                    <a class="nav-link" style="padding-left: 0; font-weight: 700; color: var(--primary);" href="<?= htmlspecialchars($baseUrl) ?>/modules/skills/exchange.php">
                        Explore Skill Offers &rarr;
                    </a>
                </article>

                <article class="card-interactive card-peach">
                    <div style="font-size: 2.3rem; margin-bottom: 12px;" class="animate-float">💡</div>
                    <h3 style="margin-top: 0; font-family: var(--font-display); color: var(--text-primary);">Community Idea Board</h3>
                    <p style="color: var(--muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 18px;">
                        Post hackathon proposals or research concepts. Upvote innovative proposals with native server-side duplicate vote prevention.
                    </p>
                    <a class="nav-link" style="padding-left: 0; font-weight: 700; color: var(--primary);" href="<?= htmlspecialchars($baseUrl) ?>/modules/ideas/index.php">
                        Browse Proposals &rarr;
                    </a>
                </article>

                <article class="card-interactive card-peach">
                    <div style="font-size: 2.3rem; margin-bottom: 12px;" class="animate-float-delayed">📅</div>
                    <h3 style="margin-top: 0; font-family: var(--font-display); color: var(--text-primary);">Campus Events</h3>
                    <p style="color: var(--muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 18px;">
                        Discover developer summits, coding sprints, and department workshops with live seat capacity tracking and one-click registrations.
                    </p>
                    <a class="nav-link" style="padding-left: 0; font-weight: 700; color: var(--primary);" href="<?= htmlspecialchars($baseUrl) ?>/modules/events/index.php">
                        View Upcoming Events &rarr;
                    </a>
                </article>

                <article class="card-interactive card-peach">
                    <div style="font-size: 2.3rem; margin-bottom: 12px;" class="animate-float">👤</div>
                    <h3 style="margin-top: 0; font-family: var(--font-display); color: var(--text-primary);">Student Profiles</h3>
                    <p style="color: var(--muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 18px;">
                        Highlight your major, semester, bio, categorized skill proficiencies, and intellectual interests to attract teammates.
                    </p>
                    <a class="nav-link" style="padding-left: 0; font-weight: 700; color: var(--primary);" href="<?= htmlspecialchars($baseUrl) ?>/modules/students/profile.php">
                        Manage Profile &rarr;
                    </a>
                </article>

                <article class="card-interactive card-peach">
                    <div style="font-size: 2.3rem; margin-bottom: 12px;" class="animate-float-delayed">🏆</div>
                    <h3 style="margin-top: 0; font-family: var(--font-display); color: var(--text-primary);">Gamification & Badges</h3>
                    <p style="color: var(--muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 18px;">
                        Earn recognition for your collaborative work. Unlock badges like "First Project", "Skill Sharer", and compete on the campus leaderboard.
                    </p>
                    <a class="nav-link" style="padding-left: 0; font-weight: 700; color: var(--primary);" href="<?= htmlspecialchars($baseUrl) ?>/modules/gamification/leaderboard.php">
                        View Leaderboard &rarr;
                    </a>
                </article>

            </div>

        </div>
    </section>


    <!-- ====================================================================
         SECTION 4: Step-by-Step "How It Works"
         ==================================================================== -->
    <section class="section-cream bg-grid-peach" style="padding: 85px 0; border-bottom: 1px solid var(--border);">
        <div class="container">

            <div style="text-align: center; max-width: 680px; margin: 0 auto 48px;" class="reveal-on-scroll">
                <span class="kicker-label kicker-peach">Simple 4-Step Process</span>
                <h2 class="font-display text-gradient-peach" style="font-size: clamp(2rem, 3.8vw, 2.7rem); margin: 12px 0 16px;">
                    How Campus Nexus Works
                </h2>
                <p style="color: var(--muted); font-size: 1.05rem;">
                    From account setup to launching cross-departmental projects in minutes.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;" class="reveal-on-scroll">

                <div class="card-interactive" style="background: white; text-align: center; padding: 30px 20px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--primary-light); color: var(--primary); font-family: var(--font-display); font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        1
                    </div>
                    <h4 style="margin: 0 0 10px; font-family: var(--font-display); font-size: 1.15rem;">Create Profile</h4>
                    <p style="color: var(--muted); font-size: 0.92rem; margin: 0; line-height: 1.6;">
                        Register your student account and select your skill catalog tags with verified proficiency levels.
                    </p>
                </div>

                <div class="card-interactive" style="background: white; text-align: center; padding: 30px 20px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--peach-100); color: var(--peach-600); font-family: var(--font-display); font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        2
                    </div>
                    <h4 style="margin: 0 0 10px; font-family: var(--font-display); font-size: 1.15rem;">Match with Teams</h4>
                    <p style="color: var(--muted); font-size: 0.92rem; margin: 0; line-height: 1.6;">
                        Explore open projects and let the compatibility engine calculate your exact percentage skill match.
                    </p>
                </div>

                <div class="card-interactive" style="background: white; text-align: center; padding: 30px 20px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--primary-light); color: var(--primary); font-family: var(--font-display); font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        3
                    </div>
                    <h4 style="margin: 0 0 10px; font-family: var(--font-display); font-size: 1.15rem;">Barter & Build</h4>
                    <p style="color: var(--muted); font-size: 0.92rem; margin: 0; line-height: 1.6;">
                        Trade knowledge on the Skill Exchange, submit ideas, and attend collaborative workshops together.
                    </p>
                </div>

                <div class="card-interactive" style="background: white; text-align: center; padding: 30px 20px;">
                    <div style="width: 44px; height: 44px; border-radius: 50%; background: var(--peach-100); color: var(--peach-600); font-family: var(--font-display); font-weight: 800; font-size: 1.2rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                        4
                    </div>
                    <h4 style="margin: 0 0 10px; font-family: var(--font-display); font-size: 1.15rem;">Earn Badges</h4>
                    <p style="color: var(--muted); font-size: 0.92rem; margin: 0; line-height: 1.6;">
                        Collect points, unlock achievement badges, and build a verified collaboration portfolio.
                    </p>
                </div>

            </div>

        </div>
    </section>


    <!-- ====================================================================
         SECTION 5: Interactive Skill Compatibility Simulator ("SkillMatch Sandbox")
         ==================================================================== -->
    <section style="padding: 85px 0; background: white; border-bottom: 1px solid var(--border);">
        <div class="container">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 40px; align-items: center;" class="reveal-on-scroll">

                <div>
                    <span class="kicker-label kicker-peach" style="margin-bottom: 12px;">Interactive Teaming Sandbox</span>
                    <h2 class="font-display text-gradient-peach" style="font-size: clamp(2rem, 3.8vw, 2.6rem); margin: 12px 0 18px;">
                        Test the Matchmaking Algorithm
                    </h2>
                    <p style="color: var(--text-secondary); line-height: 1.7; font-size: 1.05rem; margin-bottom: 20px;">
                        Click on technical skills below to simulate how our backend matchmaking engine computes your dynamic compatibility against open campus projects in real time.
                    </p>

                    <!-- Interactive Skill Selector Chips -->
                    <div style="font-size: 0.85rem; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">
                        Click skills to toggle your student profile:
                    </div>
                    <div class="sandbox-chips">
                        <span class="sandbox-chip active" data-skill="python">Python</span>
                        <span class="sandbox-chip active" data-skill="robotics">Robotics</span>
                        <span class="sandbox-chip" data-skill="machine learning">Machine Learning</span>
                        <span class="sandbox-chip" data-skill="c++">C++</span>
                        <span class="sandbox-chip" data-skill="react">React</span>
                        <span class="sandbox-chip" data-skill="mysql">MySQL</span>
                        <span class="sandbox-chip" data-skill="ui/ux design">UI/UX Design</span>
                    </div>

                    <a class="btn btn-glow" href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/team-finder.php">
                        Browse Real Campus Projects
                    </a>
                </div>

                <!-- Simulation Visual Card with Animated Circular Gauge -->
                <div class="sandbox-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <span class="status status-peach" style="font-weight: 700;">
                            Target Project: Autonomous Campus Rover
                        </span>
                        <span class="status" style="background: var(--surface-muted); color: var(--muted); font-size: 0.75rem;">
                            Campus Robotics Lab
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 24px; margin: 20px 0;">
                        <!-- SVG Circular Progress Ring -->
                        <div style="position: relative; width: 100px; height: 100px; flex-shrink: 0;">
                            <svg width="100" height="100" viewBox="0 0 100 100" style="transform: rotate(-90deg);">
                                <circle cx="50" cy="50" r="40" stroke="#f1f5f9" stroke-width="8" fill="none"></circle>
                                <circle id="sandboxProgressRing" cx="50" cy="50" r="40" stroke="var(--primary)" stroke-width="8" stroke-dasharray="251.3" stroke-dashoffset="125.65" stroke-linecap="round" fill="none" style="transition: stroke-dashoffset 0.5s ease-out, stroke 0.4s ease;"></circle>
                            </svg>
                            <div id="sandboxPercent" style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-family: var(--font-display); font-weight: 800; font-size: 1.35rem; color: var(--text-primary);">
                                50%
                            </div>
                        </div>

                        <div>
                            <h4 style="margin: 0 0 6px; font-family: var(--font-display); font-size: 1.1rem;">Required Project Skills</h4>
                            <div style="display: flex; flex-wrap: wrap; gap: 6px; font-size: 0.82rem;">
                                <span class="status status-success">Python</span>
                                <span class="status status-success">Robotics</span>
                                <span class="status status-peach">Machine Learning</span>
                                <span class="status status-peach">C++</span>
                            </div>
                        </div>
                    </div>

                    <div id="sandboxFeedback" style="background: var(--surface-peach); border: 1px solid var(--peach-200); border-radius: var(--radius-sm); padding: 14px; font-size: 0.88rem; color: var(--text-secondary); line-height: 1.6;">
                        🤝 <strong>Good Foundation!</strong> You match 2 of 4 required skills. Great opportunity to collaborate and learn on the job.
                    </div>
                </div>

            </div>

        </div>
    </section>


    <!-- ====================================================================
         SECTION 6: Official Brand Board & Visual Identity
         ==================================================================== -->
    <section class="section-cream bg-grid-peach" style="padding: 85px 0; border-bottom: 1px solid var(--border);">
        <div class="container">

            <div style="text-align: center; max-width: 720px; margin: 0 auto 48px;" class="reveal-on-scroll">
                <span class="kicker-label kicker-peach">Gamification & Recognition</span>
                <h2 class="font-display text-gradient-peach" style="font-size: clamp(2rem, 3.8vw, 2.7rem); margin: 12px 0 16px;">
                    Earn Badges & Level Up Your Profile
                </h2>
                <p style="color: var(--muted); font-size: 1.05rem;">
                    Get recognized across campus for launching projects, mentoring peers, and contributing innovative proposals.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;" class="reveal-on-scroll">

                <div class="card-interactive" style="background: white; border-top: 3px solid #6366f1;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🚀</div>
                    <h4 style="margin: 0 0 6px; font-family: var(--font-display);">First Project Badge</h4>
                    <p style="color: var(--muted); font-size: 0.88rem; margin-bottom: 12px;">Create your first collaborative project on Campus Nexus.</p>
                    <span class="status status-peach">+50 Points</span>
                </div>

                <div class="card-interactive" style="background: white; border-top: 3px solid #10b981;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">👥</div>
                    <h4 style="margin: 0 0 6px; font-family: var(--font-display);">Team Builder Badge</h4>
                    <p style="color: var(--muted); font-size: 0.88rem; margin-bottom: 12px;">Accept a student member into your project workspace.</p>
                    <span class="status status-peach">+30 Points</span>
                </div>

                <div class="card-interactive" style="background: white; border-top: 3px solid #f59e0b;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🤝</div>
                    <h4 style="margin: 0 0 6px; font-family: var(--font-display);">Skill Sharer Badge</h4>
                    <p style="color: var(--muted); font-size: 0.88rem; margin-bottom: 12px;">Accept a skill exchange request to mentor a peer.</p>
                    <span class="status status-peach">+40 Points</span>
                </div>

                <div class="card-interactive" style="background: white; border-top: 3px solid #ec4899;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">💡</div>
                    <h4 style="margin: 0 0 6px; font-family: var(--font-display);">Idea Contributor Badge</h4>
                    <p style="color: var(--muted); font-size: 0.88rem; margin-bottom: 12px;">Submit your first verified proposal to the Idea Board.</p>
                    <span class="status status-peach">+20 Points</span>
                </div>

            </div>

        </div>
    </section>


    <!-- ====================================================================
         SECTION 7: Final Conversion Call to Action
         ==================================================================== -->
    <section class="cta-section container" style="padding: 95px 0; text-align: center;">

        <span class="kicker-label kicker-peach" style="margin-bottom: 16px;">
            Join Your Campus Network
        </span>

        <h2 class="font-display" style="font-size: clamp(2.2rem, 4.2vw, 3rem); margin: 12px 0 16px; color: var(--text-primary);">
            Ready to Build Something Extraordinary?
        </h2>

        <p style="max-width: 620px; margin: 0 auto 34px; font-size: 1.1rem; color: var(--muted); line-height: 1.7;">
            Connect with motivated students, share technical knowledge, and bring your ideas to life on Campus Nexus today.
        </p>

        <div style="display: flex; justify-content: center; align-items: center; gap: 14px; flex-wrap: wrap;">
            <?php if ($isLoggedIn): ?>
                <a class="btn btn-glow" style="padding: 14px 32px; font-size: 1rem;" href="<?= htmlspecialchars($baseUrl) ?>/public/dashboard.php">
                    Go to Your Dashboard &rarr;
                </a>
            <?php else: ?>
                <a class="btn btn-glow" style="padding: 14px 34px; font-size: 1rem;" href="<?= htmlspecialchars($baseUrl) ?>/modules/auth/register.php">
                    Create Your Account
                </a>
                <a class="btn btn-secondary" style="padding: 14px 28px; font-size: 1rem;" href="<?= htmlspecialchars($baseUrl) ?>/modules/auth/login.php">
                    Sign In
                </a>
            <?php endif; ?>
        </div>

    </section>

</main>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>

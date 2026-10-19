<?php

$pageTitle = "Student Collaboration Hub";
$pageDescription = "Connect with students, discover project teams with skill matching, barter skills, vote on ideas, and attend campus workshops.";

require_once __DIR__ . "/../includes/header.php";

?>

<main>

    <!-- ====================================================================
         SECTION 1: Cinematic Cosmic Hero with Abstract Planet Sphere
         ==================================================================== -->
    <section class="hero-cosmic" style="position: relative; overflow: hidden; padding: 110px 0 120px;">
        <div class="cosmic-stars-layer" aria-hidden="true"></div>

        <!-- Ambient Atmospheric Planet Glow & Orbital Halo -->
        <div style="position: absolute; top: -120px; right: -80px; width: 480px; height: 480px; border-radius: 50%; background: radial-gradient(circle at 35% 35%, rgba(56, 189, 248, 0.28) 0%, rgba(99, 102, 241, 0.2) 45%, transparent 70%); filter: blur(35px); pointer-events: none;" aria-hidden="true"></div>
        <div style="position: absolute; bottom: -80px; left: -60px; width: 380px; height: 380px; border-radius: 50%; background: radial-gradient(circle at 50% 50%, rgba(168, 85, 247, 0.22) 0%, transparent 65%); filter: blur(45px); pointer-events: none;" aria-hidden="true"></div>

        <div class="container" style="position: relative; z-index: 2; text-align: center;">

            <div class="animate-fade-up" style="margin-bottom: 20px;">
                <span class="kicker-label kicker-cosmic">
                    ✦ Next-Generation Student Collaboration
                </span>
            </div>

            <h1 class="font-display animate-fade-up" style="font-size: clamp(2.8rem, 6.5vw, 4.8rem); line-height: 1.1; margin: 0 auto 24px; max-width: 900px;">
                Your Campus. <br>
                Your Ideas. <span class="text-gradient-cosmic">Your Future.</span>
            </h1>

            <p class="hero-description animate-fade-up" style="max-width: 720px; font-size: 1.15rem; margin: 0 auto 36px; color: var(--cosmic-text-muted); line-height: 1.7;">
                Campus Nexus connects students across departments to discover cross-functional project teams,
                exchange technical proficiencies, publish campus proposals, and register for workshops — all in one unified ecosystem.
            </p>

            <div class="hero-actions animate-fade-up" style="display: flex; justify-content: center; align-items: center; gap: 14px; flex-wrap: wrap; margin-bottom: 50px;">
                <?php if ($isLoggedIn): ?>
                    <a class="btn btn-glow" style="padding: 13px 28px; font-size: 1rem;" href="<?= htmlspecialchars($baseUrl) ?>/public/dashboard.php">
                        Go to Your Dashboard
                    </a>
                    <a class="btn btn-cosmic-outline" style="padding: 13px 26px; font-size: 1rem;" href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/team-finder.php">
                        Browse Projects
                    </a>
                <?php else: ?>
                    <a class="btn btn-glow" style="padding: 13px 30px; font-size: 1rem;" href="<?= htmlspecialchars($baseUrl) ?>/modules/auth/register.php">
                        Start Collaborating
                    </a>
                    <a class="btn btn-cosmic-outline" style="padding: 13px 26px; font-size: 1rem;" href="#features">
                        Explore Features
                    </a>
                <?php endif; ?>
            </div>

            <!-- Floating Collaborative Showcase Cards -->
            <div style="display: flex; justify-content: center; gap: 18px; flex-wrap: wrap; max-width: 860px; margin: 0 auto;" class="reveal-on-scroll">

                <div class="card-cosmic animate-float" style="padding: 14px 20px; border-radius: var(--radius-md); text-align: left; display: flex; align-items: center; gap: 14px; box-shadow: var(--shadow-cosmic);">
                    <span style="font-size: 1.8rem;">🚀</span>
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--cosmic-text-high);">AI Study Assistant</div>
                        <div style="font-size: 0.8rem; color: var(--electric-blue);">90% Skill Compatibility</div>
                    </div>
                </div>

                <div class="card-cosmic animate-float-delayed" style="padding: 14px 20px; border-radius: var(--radius-md); text-align: left; display: flex; align-items: center; gap: 14px; box-shadow: var(--shadow-cosmic);">
                    <span style="font-size: 1.8rem;">🤝</span>
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--cosmic-text-high);">Python for React</div>
                        <div style="font-size: 0.8rem; color: #a7f3d0;">Peer Skill Barter Active</div>
                    </div>
                </div>

                <div class="card-cosmic animate-float" style="padding: 14px 20px; border-radius: var(--radius-md); text-align: left; display: flex; align-items: center; gap: 14px; box-shadow: var(--shadow-cosmic);">
                    <span style="font-size: 1.8rem;">🏆</span>
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: var(--cosmic-text-high);">Team Builder Badge</div>
                        <div style="font-size: 0.8rem; color: #fde68a;">+30 Points Awarded</div>
                    </div>
                </div>

            </div>

        </div>
    </section>


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
                    Campus project recruiting is usually locked behind private group chats, department silos, and disorganized flyers. Here is how Campus Nexus transforms student collaboration.
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
         SECTION 5: Collaboration & Algorithmic Skill Matching Spotlight
         ==================================================================== -->
    <section style="padding: 85px 0; background: white; border-bottom: 1px solid var(--border);">
        <div class="container">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 40px; align-items: center;" class="reveal-on-scroll">

                <div>
                    <span class="kicker-label kicker-peach" style="margin-bottom: 12px;">Data-Driven Synergy</span>
                    <h2 class="font-display text-gradient-peach" style="font-size: clamp(2rem, 3.8vw, 2.6rem); margin: 12px 0 18px;">
                        Smart Compatibility Engine
                    </h2>
                    <p style="color: var(--text-secondary); line-height: 1.7; font-size: 1.05rem; margin-bottom: 20px;">
                        Never guess whether you are qualified for a project. When you browse the Project Finder, Campus Nexus matches your catalogued skills against each project’s required stack.
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="color: var(--success); font-weight: 800; font-size: 1.2rem;">✓</span>
                            <span style="color: var(--text-primary); font-size: 0.95rem;"><strong>Instant Percentages:</strong> Real-time match scores calculated in PHP/MySQL.</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="color: var(--success); font-weight: 800; font-size: 1.2rem;">✓</span>
                            <span style="color: var(--text-primary); font-size: 0.95rem;"><strong>Transparent Breakdowns:</strong> See which skills match and which you can learn on the job.</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="color: var(--success); font-weight: 800; font-size: 1.2rem;">✓</span>
                            <span style="color: var(--text-primary); font-size: 0.95rem;"><strong>Anti-Spam Requests:</strong> Project creators review pending requests before accepting members.</span>
                        </div>
                    </div>

                    <a class="btn btn-glow" href="<?= htmlspecialchars($baseUrl) ?>/modules/projects/team-finder.php">
                        Try Team Finder Now
                    </a>
                </div>

                <!-- Simulation Visual Card -->
                <div class="card-interactive card-peach" style="padding: 28px; box-shadow: var(--shadow-lg);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <span class="status" style="background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; font-weight: 700;">
                            Live Simulation
                        </span>
                        <span class="status" style="background: var(--primary-light); color: var(--primary); font-weight: 800;">
                            75% Match
                        </span>
                    </div>

                    <h3 style="font-family: var(--font-display); margin: 0 0 8px; color: var(--text-primary);">
                        Autonomous Rover Navigation
                    </h3>
                    <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 16px;">
                        Building computer-vision pathfinding algorithms for campus competition.
                    </p>

                    <div style="background: white; border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 14px; margin-bottom: 16px;">
                        <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--muted); margin-bottom: 8px;">
                            Skills Matched: 3 of 4 Required
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                            <span class="status status-success">✓ Python</span>
                            <span class="status status-success">✓ Robotics</span>
                            <span class="status status-success">✓ C++</span>
                            <span class="status" style="background: #f1f5f9; color: #64748b; border: 1px dashed #cbd5e1;">ROS2 (Learning Opportunity)</span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.85rem; color: var(--muted);">Created by: <strong>Priya S. (Robotics)</strong></span>
                        <span class="btn btn-sm btn-glow" style="cursor: default; pointer-events: none;">Join Team</span>
                    </div>
                </div>

            </div>

        </div>
    </section>


    <!-- ====================================================================
         SECTION 6: Gamification, Points & Leaderboard Spotlight
         ==================================================================== -->
    <section class="section-cream bg-grid-peach" style="padding: 85px 0; border-bottom: 1px solid var(--border);">
        <div class="container">

            <div style="text-align: center; max-width: 680px; margin: 0 auto 48px;" class="reveal-on-scroll">
                <span class="kicker-label kicker-peach">Reward System</span>
                <h2 class="font-display text-gradient-peach" style="font-size: clamp(2rem, 3.8vw, 2.7rem); margin: 12px 0 16px;">
                    Recognizing Collaborative Leadership
                </h2>
                <p style="color: var(--muted); font-size: 1.05rem;">
                    Every positive collaborative action earns points and unlocks permanent badges recorded directly to your student record.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;" class="reveal-on-scroll">

                <div class="card-interactive" style="background: white; border-top: 3px solid #6366f1;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🚀</div>
                    <h4 style="margin: 0 0 6px; font-family: var(--font-display);">First Project</h4>
                    <p style="color: var(--muted); font-size: 0.88rem; margin-bottom: 12px;">Create your first collaborative project on Campus Nexus.</p>
                    <span class="status status-peach">+50 Points</span>
                </div>

                <div class="card-interactive" style="background: white; border-top: 3px solid #10b981;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">👥</div>
                    <h4 style="margin: 0 0 6px; font-family: var(--font-display);">Team Builder</h4>
                    <p style="color: var(--muted); font-size: 0.88rem; margin-bottom: 12px;">Accept a student member into your project workspace.</p>
                    <span class="status status-peach">+30 Points</span>
                </div>

                <div class="card-interactive" style="background: white; border-top: 3px solid #f59e0b;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">🤝</div>
                    <h4 style="margin: 0 0 6px; font-family: var(--font-display);">Skill Sharer</h4>
                    <p style="color: var(--muted); font-size: 0.88rem; margin-bottom: 12px;">Accept a skill exchange request to mentor a peer.</p>
                    <span class="status status-peach">+40 Points</span>
                </div>

                <div class="card-interactive" style="background: white; border-top: 3px solid #ec4899;">
                    <div style="font-size: 2rem; margin-bottom: 10px;">💡</div>
                    <h4 style="margin: 0 0 6px; font-family: var(--font-display);">Idea Contributor</h4>
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

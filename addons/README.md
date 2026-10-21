# Addons

Optional modules (Wallet, Loyalty, Auction, AI, POS, Subscription, etc.)
live here, physically separated from app/Domain/* (core). Each addon
registers its own service provider, routes, and migrations, and depends on
core Domain services through their public interfaces only — never on core
tables directly. Populated starting Phase 22.

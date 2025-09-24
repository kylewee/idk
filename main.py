#!/usr/bin/env python3
"""
IDK - A simple decision-making helper tool
When you don't know what to choose, let IDK help!
"""

import random
import sys
from typing import List


def make_decision(options: List[str]) -> str:
    """Make a random decision from the given options."""
    if not options:
        return "IDK... you didn't give me any options!"
    
    return random.choice(options)


def coin_flip() -> str:
    """Simple coin flip decision."""
    return random.choice(["Heads", "Tails"])


def yes_no() -> str:
    """Simple yes/no decision."""
    return random.choice(["Yes", "No"])


def magic_8_ball() -> str:
    """Magic 8-ball style responses."""
    responses = [
        "It is certain",
        "Reply hazy, try again",
        "Don't count on it",
        "It is decidedly so",
        "My sources say no",
        "Yes definitely",
        "Better not tell you now",
        "Outlook not so good",
        "You may rely on it",
        "Concentrate and ask again",
        "Very doubtful",
        "As I see it, yes",
        "My reply is no",
        "Without a doubt",
        "Cannot predict now",
        "Most likely",
        "Ask again later",
        "Signs point to yes",
        "Outlook good",
        "Don't count on it"
    ]
    return random.choice(responses)


def main():
    """Main function to handle command line usage."""
    if len(sys.argv) == 1:
        print("IDK - Decision Making Helper")
        print("Usage:")
        print("  python main.py <option1> <option2> ... - Choose from options")
        print("  python main.py --coin - Flip a coin")
        print("  python main.py --yes-no - Yes or No decision")
        print("  python main.py --8ball - Magic 8-ball response")
        return
    
    if "--coin" in sys.argv:
        print(f"Coin flip result: {coin_flip()}")
    elif "--yes-no" in sys.argv:
        print(f"Decision: {yes_no()}")
    elif "--8ball" in sys.argv:
        print(f"Magic 8-ball says: {magic_8_ball()}")
    else:
        options = [arg for arg in sys.argv[1:] if not arg.startswith("--")]
        if options:
            result = make_decision(options)
            print(f"IDK, but I choose: {result}")
        else:
            print("IDK... no valid options provided!")


if __name__ == "__main__":
    main()
#!/usr/bin/env python3
"""
Tests for the IDK decision making helper
"""

import unittest
from main import make_decision, coin_flip, yes_no, magic_8_ball


class TestIDK(unittest.TestCase):
    
    def test_make_decision_with_options(self):
        """Test that make_decision returns one of the provided options."""
        options = ["option1", "option2", "option3"]
        result = make_decision(options)
        self.assertIn(result, options)
    
    def test_make_decision_empty_options(self):
        """Test that make_decision handles empty options gracefully."""
        result = make_decision([])
        self.assertEqual(result, "IDK... you didn't give me any options!")
    
    def test_coin_flip(self):
        """Test that coin_flip returns either Heads or Tails."""
        result = coin_flip()
        self.assertIn(result, ["Heads", "Tails"])
    
    def test_yes_no(self):
        """Test that yes_no returns either Yes or No."""
        result = yes_no()
        self.assertIn(result, ["Yes", "No"])
    
    def test_magic_8_ball(self):
        """Test that magic_8_ball returns a valid response."""
        result = magic_8_ball()
        self.assertIsInstance(result, str)
        self.assertGreater(len(result), 0)
    
    def test_multiple_calls_vary(self):
        """Test that multiple calls can produce different results (probabilistic)."""
        # This test might occasionally fail due to randomness, but it's very unlikely
        results = set()
        for _ in range(10):
            results.add(make_decision(["a", "b", "c", "d", "e"]))
        # We expect some variation in 10 calls with 5 options
        self.assertGreater(len(results), 1, "Expected some variation in results")


if __name__ == "__main__":
    unittest.main()
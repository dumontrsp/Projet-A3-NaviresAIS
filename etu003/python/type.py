#!/usr/bin/python3
###
# \\Author: Thibault Napoléon "Imothep"
# \\Company: ISEN Ouest
# \\Email: thibault.napoleon@isen-ouest.yncrea.fr
# \\Created Date: 02-Jun-2023 - 23:23:21
# \\Last Modified: 17-Jun-2025 - 16:29:45
###

"""Predict vessel type."""

# Imports.
import argparse


def checkArguments():
    """Check program arguments and return program parameters."""
    parser = argparse.ArgumentParser()
    parser.add_argument('--model', type=str, required=True,
                        help='Model to use for prediction (knn, svm, rf, mlp)')
    parser.add_argument('--sog', type=float, required=True,
                        help='Speed Over Ground')
    parser.add_argument('--cog', type=float, required=True,
                        help='Course Over Ground')
    parser.add_argument('--latitude', type=float, required=True,
                        help='Latitude')
    parser.add_argument('--longitude', type=float, required=True,
                        help='Longitude')
    parser.add_argument('--heading', type=float, required=True,
                        help='True heading angle')
    parser.add_argument('--length', type=float, required=True,
                        help='Length of vessel')
    parser.add_argument('--width', type=float, required=True,
                        help='Width of vessel')
    parser.add_argument('--draft', type=float, required=True,
                        help='Draft depth of vessel')
    parser.add_argument('--status', type=int, required=True,
                        help='Navigation status')
    parser.add_argument('-t', '--time', type=str, required=True,
                        help='Full UTC date and time')
    return parser.parse_args()


# Main program.
args = checkArguments()

# Predict type (random prediction for the example).
if args.model == 'knn':
    print(30)
if args.model == 'svm':
    print(15)
if args.model == 'rf':
    print(25)
if args.model == 'mlp':
    print(23)

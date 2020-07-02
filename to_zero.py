import gpxpy
import gpxpy.gpx
import argparse

INITIALGPSPOINTLAT = -19.106371
INITIALGPSPOINTLONG = -169.870977

parser = argparse.ArgumentParser()
parser.add_argument("--input", "-i", type=str, required=True)
parser.add_argument("--output", "-o", type=str, required=True)
args = parser.parse_args()
gpx_file = open(args.input, 'r')
gpx = gpxpy.parse(gpx_file)
for track in gpx.tracks:
    for segment in track.segments:
        for point in segment.points:
            point.latitude -= INITIALGPSPOINTLAT
            point.longitude -= INITIALGPSPOINTLONG
with open(args.output, 'w') as f:
    f.write(gpx.to_xml())

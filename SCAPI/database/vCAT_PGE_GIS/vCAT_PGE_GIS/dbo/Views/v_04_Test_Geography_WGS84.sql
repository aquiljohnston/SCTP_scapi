CREATE VIEW dbo.v_04_Test_Geography_WGS84 AS 

SELECT 
 ROW_NUMBER() OVER(ORDER BY [Latitude], [Longitude]) AS ObjectID
,[LandmarkName]
,[Location]
,[Latitude]
,[Longitude]
--,GEOMETRY::STPointFromText('POINT(' + CAST([Longitude] AS VARCHAR(20)) + ' ' + CAST([Latitude] AS VARCHAR(20)) + ')', 26910) AS Shape_Geometry_NAD83_UTM_Z10N
--,GEOMETRY::STPointFromText('POINT(' + CAST([Longitude] AS VARCHAR(20)) + ' ' + CAST([Latitude] AS VARCHAR(20)) + ')', 4326) AS Shape_Geometry_WGS84
--,GEOGRAPHY::Point([Longitude], [Latitude], 4326) AS Shape_Geography__NAD83_UTM_Z10N
,GEOGRAPHY::Point([Longitude], [Latitude], 4326) AS Shape_Geography_WGS84
FROM [dbo].[zLandmarks]
WHERE Location like '%CA'
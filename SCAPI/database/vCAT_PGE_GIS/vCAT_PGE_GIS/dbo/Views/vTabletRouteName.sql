Create View vTabletRouteName
AS
SELECT DISTINCT
 mg.MapGridUID
,mgp.LONG_ROUTE_NAME AS RouteName
FROM
 [dbo].[rgMapGridPipeline] mgp
LEFT  JOIN [dbo].[rgMapGridLog] mg ON 
  mgp.FUNCTIONALLOCATION = mg.FLOC
WHERE 
mgp.FUNCTIONALLOCATION IS NOT NULL
AND mg.MapGridUID IS NOT NULL
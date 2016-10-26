
CREATE Procedure [dbo].[spDynamicOQbyUser]
AS
DECLARE @cols AS NVARCHAR(MAX),
    @Dynamicquery  AS NVARCHAR(MAX),
	@Query as Nvarchar(max),
	@BaseQuery1 as Nvarchar(max),
	@BaseQuery2 as Nvarchar(max)

Set @BaseQuery1 = 'select u.UserUID, u.UserFirstName, u.UserLastName, u.UserLANID, u.UserEmployeeType ,'
Set @BaseQuery2 = 'from UserTb u left Join'

select @cols = STUFF((SELECT ',' + QUOTENAME(OQProfile)
                    from tInspectorOQLog
                    group by OQProfile
                    order by OQProfile
            FOR XML PATH(''), TYPE
            ).value('.', 'NVARCHAR(MAX)') 
        ,1,1,'')

set @Dynamicquery = N'SELECT UserUID, ' + @cols + N' from 
             (
                select UserUID, OQExpireDate, OQProfile
                from tInspectorOQLog
            ) x
            pivot 
            (
                max(OQExpireDate)
                for OQProfile in (' + @cols + N')
            ) p '


Set @Query = @BaseQuery1 + @cols + @BaseQuery2 + ' (' + @Dynamicquery + ') OQ on u.UserUID = OQ.UserUID'
--select @BaseQuery
--select @query


exec sp_executesql @Query;

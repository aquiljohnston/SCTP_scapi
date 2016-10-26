






CREATE PROCEDURE [dbo].[JSONProcessTabletInsert]
(
      @JSON_Str VarChar(Max)
    
)
AS 

BEGIN TRY

	Declare @TransactionType VarChar(20)
		,@SingleQuote char(1)
		,@SQLQuery varchar(max)
		,@ReturnVal int = 1

	Set @SingleQuote = CHAR(39)

	Set @JSON_Str = REPLACE(@JSON_Str, @SingleQuote, '')

	Set @TransactionType = SUBSTRING(@JSON_Str, 2, CHARINDEX(':',@JSON_Str,2)-3)
	/*
	IF @TransactionType = 'breadcrumb'
		BEGIN
		   EXEC JSON_Breadcrumb @JSON_Str
		END
	ELSE
	IF @TransactionType = 'activity'
		BEGIN
		   EXEC JSON_Activity @JSON_Str
		END
	ELSE
	IF @TransactionType = 'indication'
		BEGIN
		   EXEC JSON_Indication @JSON_Str
		END
	ELSE
	IF @TransactionType = 'equipment'
		BEGIN
		   EXEC JSON_Equipment @JSON_Str
		END
	ELSE
	IF @TransactionType = 'assetInspection'
		BEGIN
		   EXEC [dbo].[JSON_AssetInspection] @JSON_Str
		END
	ELSE
	*/
	IF @TransactionType = 'lodInspection'
		BEGIN
		   --EXEC [dbo].[JSON_LODInspection] @JSON_Str
		   Print ''
		END


	ELSE
	BEGIN

		SET NOCOUNT ON
		
		Insert Into [dbo].[tTabletDataInsertError] (
			InsertedData
			, Comments
		)
		Select
			@JSON_Str
			,'Unknown Transaction Type'
		
		--Set @SQLQuery = 'Insert Into [dbo].[t_TabletDataInsertError] (InsertedData, Comments)
	                 -- Values (' + @SingleQuote + @JSON_Str + @SingleQuote + ', ' + @SingleQuote + 'Unknown Transaction Type' + @SingleQuote + ')'
					   
		--EXEC (@SQLQuery)

		SET NOCOUNT OFF

	END

END TRY
BEGIN CATCH

	SET NOCOUNT ON

	Insert Into [dbo].[tTabletDataInsertError] (
			InsertedData
			, Comments
			, ErrorNumber
			, ErrorMessage
		)
		Select
			@JSON_Str
			,'JSON Error'
			,ERROR_NUMBER()
			,ERROR_MESSAGE()
		
	--Set @SQLQuery = 'Insert Into [dbo].[t_TabletDataInsertError] (InsertedData, Comments)
	       -- Values (' + @SingleQuote + @JSON_Str + @SingleQuote + ', ' + @SingleQuote + 'JSON ERROR' + @SingleQuote + ')'
					   
	--EXEC (@SQLQuery)

	SET NOCOUNT OFF

	
	
END CATCH

Select @ReturnVal






